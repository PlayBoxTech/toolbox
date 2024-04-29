@extends('layout2')


@section('header')
    Results for {{ $real_domain }}
@endsection

@section('body')
        <div class="container">
           <table class="table is-fullwidth is-striped">
            <thead><tr><th colspan = 2 class="has-text-centered">NameServers</th></tr></thead>
            <tr><td><strong>Using CloudFlare?</strong></td>
                <td>@if($cloudflare) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>Using InMotion Hosting's NameServers?<br> (Including Vanity)</strong></td>
                <td>@if($imh) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>Using ServConfig's NameServers? <br> (Including Vanity)</strong></td>
                <td>@if($servconfig) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>Using Web Hosting Hub's Nameservers?<br> (Including Vanity)</strong></td>
                <td>@if($whh) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>Using GoDaddy's NameServers?</strong></td>
                <td>@if($godaddy) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>DNSKEY Detected?</strong></td>
                <td>@if($dnssec) Yes
                    @else No
                    @endif
                </td>
            </tr>
        </table>
        <table class="table is-fullwidth is-striped">
            <thead><tr><th colspan = 2 class="has-text-centered">eMail servers</th></tr></thead>
            <tr><td><strong>SOA Matches DNS</strong></td>
                <td>@if($soa) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>DMARC Detected</strong></td>
                <td>@if($dmarc) Yes
                    @else No
                    @endif
                </td>
            </tr>
            <tr><td><strong>SPF Detected</strong></td>
                <td>@if($spf) Yes
                    @else No
                    @endif
                </td>
            </tr>
            @if($spf)
            <tr><td><strong>SPF Valid</strong></td>
                <td>@if($checkResult) Yes
                    @else No
                    @endif
                </td>
            </tr>
            @if(!$checkResult)
            <tr><td><strong>Issues with SPF</strong><td>
                <td>@foreach ($issues as $element)
                    {{ $element }}
                  @endforeach
                </td></tr>
            @endif
            @endif
            <tr><td><strong>Google Verify Code Detected</strong></td>
                <td>@if($google) Yes
                    @else No
                    @endif
                </td>
            </tr>
        </table>
@endsection
