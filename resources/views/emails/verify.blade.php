@extends('emails.layouts.base')

@section('title', 'Verify your email — Tenant Workspace')

@section('content')

  {{-- Icon --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#eff6ff;border-radius:50%;width:56px;height:56px;line-height:56px;font-size:26px;">
      ✉️
    </div>
  </div>

  {{-- Heading --}}
  <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0f172a;text-align:center;">
    Verify your email address
  </h1>
  <p style="margin:0 0 28px;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
    Hi <strong style="color:#0f172a;">{{ $user->name ?? 'there' }}</strong>, thanks for signing up!<br />
    Click the button below to confirm your email and activate your account.
  </p>

  {{-- CTA Button --}}
  <div style="text-align:center;margin-bottom:28px;">
    <a href="{{ $url }}"
       style="display:inline-block;padding:13px 32px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;letter-spacing:.01em;">
      Verify Email Address
    </a>
  </div>

  {{-- Fallback URL --}}
  <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0 0 24px;line-height:1.6;">
    If the button doesn't work, copy and paste this link into your browser:<br />
    <a href="{{ $url }}" style="color:#3b82f6;word-break:break-all;">{{ $url }}</a>
  </p>

  {{-- Divider --}}
  <hr style="border:none;border-top:1px solid #f1f5f9;margin:0 0 20px;" />

  {{-- Footer note --}}
  <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0;line-height:1.6;">
    This link expires in <strong>60 minutes</strong>. If you didn't create an account, you can safely ignore this email.
  </p>

@endsection
