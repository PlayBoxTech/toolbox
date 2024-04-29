@extends('layout')

@section('title', 'Toolbox')
@section('content')
            <h1 class="title has-text-centered">
                Toolbox
            </h1>
           <form method="POST" action="/test">
            @csrf
            Welcome to the Toolbox Domain Test Site!
            <div class="field">
                <label class="label">Domain Name to Test</label>
                <div class="control">
                    <input class="input" type="text" name="domain" placeholder="example.com">
                </div>
            </div>
            <div class="field">
                <input id="switchRoundedInfo" type="checkbox" name="simpleResults" class="switch is-rounded is-info" checked="checked">
                <label for="switchRoundedInfo">Simple Results</label>
              </div>
            <div class="field is-grouped">
                <div class="control">
                  <button class="button is-link">Submit</button>
                </div>
            </div>
           </form>
        </div>
@endsection
