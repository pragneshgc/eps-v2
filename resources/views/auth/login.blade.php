@extends('layouts.auth')

@section('content')
    <div class="loginBox">
        <p class="info">Your IP: <span>{{ \App\Helpers\Generic::getIP() }}</span></p>
        <h1>Login</h1>
        @if (config('app.env') != 'production')
            <p class="demo-warning">The Application is running in DEMO mode!</p>
        @endif

        <form id="password-login" method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
            @csrf
            <div class="formItemsGroup">
                <div class="formItem">
                    <label class="formItemLabel">Username:</label>
                    <input id="email" type="email" class="tBox tBoxSize01" name="email" value="{{ old('email') }}"
                        required autofocus />
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="formItem">
                    <label class="formItemLabel">Password:</label>
                    <input id="password" type="password" class="tBox tBoxSize01" name="password" required />
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="formItem">
                    <label class="checkboxElement">
                        <input class="custom-control-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>Remember me</label>
                </div>
            </div>
            <div class="footer">
                <div class="formItemsGroup"><button type="submit" class="btn btnSize01 primaryBtn">Login</button></div>
            </div>
        </form>
    </div>
@endsection

<style>
    @font-face {
        font-family: 'dotsfont';
        src: url('/fonts/dotsfont.eot');
        src: url('/fonts/dotsfont.eot?#iefix') format('embedded-opentype'),
            url('/fonts/dotsfont.woff') format('woff'),
            url('/fonts/dotsfont.ttf') format('truetype'),
            url('/fonts/dotsfont.svg#dotsfontregular') format('svg');
    }

    [conceal] {
        font-family: 'dotsfont';
        font-size: 12px;
    }

    .demo-warning {
        margin: 0;
        background: red;
        color: white;
        text-align: center;
        font-weight: 700;
        font-size: 16px;
    }

    .login {
        display: flex;
        flex-direction: column;
    }

    .loginBox {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex-grow: 1;
    }

    .loginBox h1 {
        text-align: center !important;
        color: white !important;
    }

    .nav {
        justify-content: center;
        display: inline-flex;
        width: 100%;
    }

    .nav-button {
        margin: 10px;
    }

    .formItemLabel {
        color: white !important;
        font-weight: 700 !important;
    }

    .checkboxElement {
        color: white !important;
        font-weight: 700 !important;

    }

    .page-login .login .loginBox .footer {
        background: white !important;
    }
</style>
