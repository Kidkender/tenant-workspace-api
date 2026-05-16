@extends('emails.layouts.base')

@section('title', 'Reset your password — Tenant Workspace')

@section('content')

  {{-- Icon --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#fef3c7;border-radius:50%;width:56px;height:56px;line-height:56px;font-size:26px;">
      🔑
    </div>
  </div>

  {{-- Heading --}}
  <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0f172a;text-align:center;">
    Reset your password
  </h1>
  <p style="margin:0 0 28px;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
    Hi <strong style="color:#0f172a;">{{ $name }}</strong>,<br />
    We received a request to reset your password. Use the code below to proceed.
  </p>

  {{-- OTP Code --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#fef3c7;border:2px dashed #f59e0b;border-radius:12px;padding:16px 40px;">
      <span style="font-size:32px;font-weight:700;color:#d97706;letter-spacing:8px;">{{ $code }}</span>
    </div>
  </div>

  {{-- Divider --}}
  <hr style="border:none;border-top:1px solid #f1f5f9;margin:0 0 20px;" />

  {{-- Footer note --}}
  <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0;line-height:1.6;">
    This code expires in <strong>15 minutes</strong>. If you didn't request a password reset, you can safely ignore this email.
  </p>

@endsection
