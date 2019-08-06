@extends('layouts.landing')

@section('body')
    <section class="section section-shaped section-lg my-0">
        <div class="shape shape-style-1 bg-gradient-default">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="container pt-lg-md">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card border-0 shadow bg-secondary">
                        <div class="card-body px-lg-5 py-lg-5">
                            <div class="text-muted text-center mb-3">
                                <small>Sign in with</small>
                            </div>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="btn-wrapper text-center">
                                    <button type="button" class="btn btn-icon btn-neutral">
                                        <span class="btn-inner--icon"><img src="{{asset('img/icons/google.svg')}}"></span>
                                        <span class="btn-inner--text"> Google</span>
                                    </button>
                                </div>
                                <div class="text-center text-muted mb-4"><small>Or sign in with credentials</small></div>
                                <div class="form-group mb-3 input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-email"></i></span>
                                    </div>
                                    <input id="email" type="email" placeholder="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                </div>
                                <div class="form-group input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input id="password" type="password" placeholder="Password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remember">
                                                {{ __('Remember Me') }}
                                    </label>
                                </div>

                                <div class="text-center">
                                        <button type="submit" class="btn my-4 btn-primary">
                                            {{ __('Login') }}
                                        </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <a href="{{ route('password.request') }}" class="text-light">
                                <small>{{ __('Forgot Your Password?') }}</small>
                            </a>
                        </div>
                        <div class="col-6 text-right">
                            <a href="#" class="text-light">
                                <small>Create new account</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
