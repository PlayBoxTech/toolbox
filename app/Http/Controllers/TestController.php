<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use Spatie\Dns\Dns;
use Spatie\Rdap\Facades\Rdap;
use Spatie\Rdap\Enums\DomainStatus;
use Pdp\Rules;
use Pdp\Domain;


/*
TODO: while some of the code here will catch servers connected to servers outside of the US,
still need to add IPs to check for vanity outside of the US

 */

class TestController extends Controller
{
    protected $dns;

    public function __construct()
    {
        $this->dns = new Dns();
    }

    public function test()
    {
        $attributes = request()->validate([
            'domain' => [
                'required',
                'min:3',
                'regex:/^([a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/',
            ],
        ]);
        $domain = $attributes['domain'];
        $simpleRequest = request()->input('simpleResults');

        if ($simpleRequest)
        {
            $this->simpleResults($domain); 
        }
        //dd($simpleRequest);
    }

    function simpleResults($domain)
    {
    
            
        $publicSuffixList = Rules::fromPath('https://publicsuffix.org/list/public_suffix_list.dat');



        //$domain = request()->validated('domain_name');

        // Special DNS watch cases:
        $cloudflare = false;
        $imh = false;
        $servconfig = false;
        $whh = false;
        $godaddy = false;
        $dnssec = false;
        $dnskey = false;
        $ds = false;

        // Email records:
        $dmarc = false;
        $spf = false;
        $dkim = false;
        $soa = false;
        $google = false;
        $checkResult = false;
        $spfstring = '';
        $issues = [];
        $cfproxy = false;

       // $reader = new Reader('app/GeoLite2-City.mmdb');

       
        $domain2 = Domain::fromIDNA2008($domain);
        $pdpresult = $publicSuffixList->resolve($domain2)->registrableDomain()->toString();
        
        // ddd($domain,  $pdpresult);

        if ($pdpresult <> $domain)
        {
            $domain = $pdpresult;
        }

        if (rdap::domainIsSupported($domain)) { 
            $rdap = Rdap::domain($domain);
            $rdp = true;
            $registertration = $rdap->registrationDate()->format('Y-m-d');
            $expiration = $rdap->expirationDate()->format('Y-m-d');
            $domain_lock = $rdap->hasStatus(DomainStatus::ClientTransferProhibited);
            }
        
        else
        {
            $rdp = false; 
            $registertration = '0000-00-00';
            $expiration = '0000-00-00';
        }

      

        // Get the A, NS, MX, TXT records for the domain.
        $array_a = [];
        $array_ns = [];
        $array_mx = [];
        $array_txt = [];
        $array_soa = [];

        $records = $this->dns->getRecords($domain, ['A', 'NS', 'MX', 'TXT', 'SOA', 'DNS_DNSKEY', 'DNS_DS']);

        // ddd($records);

        if (empty($records)) {

            return view('domain-error')->with([
                'title' => 'Whoops!',
                'domain' => $domain,
                'error' => 'Domain Not Found',
            ]);
        }
        //ddd($records);

        // Store actual domain name (without http:// or https:// prefix)
        /* $real_domain = $records[0]->host();

        if ($domain<>$real_domain)
        {
        ddd($domain, $real_domain);
        }*/

       

        // loop through records
        foreach ($records as $record) {
            // create switch based on record type
            switch ($record->type()) {
                case 'A':
                    $array_a[] = $record->ip();
                    break;
                case 'NS':
                    $array_ns[] = $record->target();
                    break;
                case 'MX':
                    $array_mx[] = $record->target();
                    break;
                case 'TXT':
                    $array_txt[] = $record->txt();
                    break;
                case 'SOA':
                    $array_soa[] = $record->mname();
                    break;
                case 'DNS_DNSKEY':
                    $dnskey = true;
                    break;
                case 'DNS_DS':
                    $ds = true;
                    break;
                default:
                    // do something with other record types
                    break;
            }
        }

        /*
        Sanity Check on domain

         */

        if (empty($array_a)) {
            return view('domain-error')->with([
                'title' => 'Whoops!',
                'domain' => $domain,
                'error' => 'Domain Doesn\'t have A records, doubting its registered',
            ]);
        }

        /*

        DOMAIN TESTS

         */

        // checking array_ns to see if it contains 'cloudflare.com' / 'inmotionhosting.com' / 'servconfig.com' / 'webhostinghub.com' / 'domaincontrol.com'
        foreach ($array_ns as $element) {
            if (strpos($element, 'cloudflare.com') !== false) {
                $cloudflare = true;
                break;
            }

            if (strpos($element, 'inmotionhosting.com') !== false) {
                $imh = true;
                break;
            }

            if (strpos($element, 'servconfig.com') !== false) {
                $servconfig = true;
                break;
            }

            if (strpos($element, 'webhostinghub.com') !== false) {
                $whh = true;
                break;
            }

            if (strpos($element, 'domaincontrol.com') !== false) {
                $godaddy = true;
                break;
            }
        }

        $imh_ips = ['74.124.210.242', '70.39.150.2', '213.165.240.101', '213.165.240.102'];
        $serv_ips = ['216.194.168.112', '70.39.146.236', '213.165.240.101', '213.165.240.102'];
        // checking to see if imh, servconfig, or whh are still false, then checking to see if vanity ns are being used
        if ($imh == false && $servconfig == false && $whh == false) {
            // check to see if $this.check_ns_ip is equal to 74.124.210.242 or 70.39.150.2
            //if ($this->check_ns_ip($array_ns[0]) == '74.124.210.242' || $this->check_ns_ip($array_ns[0]) == '70.39.150.2') || $this->check_ns_ip($array_ns[0]) == '213.165.240.101')
            $ips = $this->check_ns_ips($array_ns);

            if (array_intersect($imh_ips, $ips)) {
                $imh = true;
            }
            if (array_intersect($serv_ips, $ips)) {
                $servconfig = true;
            }

            if ($this->check_ns_ip($array_ns[0]) == '209.182.197.185' || $this->check_ns_ip($array_ns[0]) == '173.205.127.4') {
                $whh = true;
            }
        }

        $dnssec = $dnskey && $ds;

        /*

        Email Tests

         */

        // Check to see if DMARC exists:
        $dmarc = $this->isDMARCValid($domain);

        // check $array_txt for spf
        foreach ($array_txt as $element) {

            if (strpos($element, 'v=spf1') !== false) {
                $spf = true;
                $spfstring = $element;
                //$spfcount = $this->countIPAddressesInSPF($element);
                //echo "SPF is $element";

            }
            if (strpos($element, 'google-site-verification=') !== false) {
                $google = true;

            }
        }

        // Checking to see if SPF is valid:
        if ($spf) {
            $environment = new \SPFLib\Check\Environment($array_a[0], $domain, 'sender@' . $domain);
            $checker = new \SPFLib\Checker();
            $checkResult = $checker->check($environment);

        }

        // If the SPF is not valid, what is wrong?
        if (!$checkResult && $spf) {
            $record = (new \SPFLib\Decoder())->getRecordFromTXT($spfstring);
            $issues = (new \SPFLib\SemanticValidator())->validate($record);
        }

        // see if $array_soa matches any of the $array_ns values
        foreach ($array_soa as $element) {
            if (in_array($element, $array_ns)) {
                $soa = true;
            }
        }

        /*

        Output Results

         */

        return view('test_results')->with([
            'title' => 'Test Results',
            'cloudflare' => $cloudflare,
            'imh' => $imh,
            'servconfig' => $servconfig,
            'whh' => $whh,
            'real_domain' => $domain,
            'godaddy' => $godaddy,
            'dmarc' => $dmarc,
            'spf' => $spf,
            'soa' => $soa,
            'google' => $google,
            'dnssec' => $dnssec,
            'checkResult' => $checkResult,
            'registertration' =>  $registertration,
            'expiration' => $expiration,
            'rdp' => $rdp, 
            'domain_lock' => $domain_lock,
        ]);

    }

    /*

    Private Functions

     */

   

    private function check_ns_ip($ns)
    {
        // $dns = new Dns();
        $records = $this->dns->getRecords($ns, ['A']);
        $ip = $records[0]->ip();
        return $ip;

    }

    private function check_ns_ips($nses)
    {
        $ips = [];
        foreach ($nses as $ns) {
            $records = $this->dns->getRecords($ns, ['A']);
            $ips[] = $records[0]->ip();
        }
        return $ips;
    }

    private function isDMARCValid($domain)
    {
        // Perform DNS query to get DMARC records for the domain
        $dmarcRecords = dns_get_record("_dmarc." . $domain, DNS_TXT);

        // Check if DMARC records are found
        if (empty($dmarcRecords)) {
            // echo "No DMARC records found for the domain.\n";
            //echo "empty dmarc";
            return false;
        }

        // Iterate through DMARC records
        foreach ($dmarcRecords as $record) {
            // Check if the DMARC record contains the required tags (e.g., v, p, rua, ruf)
            if (isset($record['txt'])) {
                $dmarcTags = explode(";", $record['txt']);
                $requiredTags = ['v', 'p'];

                foreach ($requiredTags as $tag) {
                    $tagFound = false;

                    foreach ($dmarcTags as $dmarcTag) {
                        if (strpos($dmarcTag, $tag . '=') === 0) {

                            $tagFound = true;
                            break;
                        }
                    }

                    if (!$tagFound) {
                        //echo "DMARC record is missing the required tag: $tag\n";
                        return false;
                    } else {
                        return true;
                    }

                }
            }
        }
    }

}
