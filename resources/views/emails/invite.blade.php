@extends('emails.layouts.base')

@section('title', 'You\'ve been invited — Tenant Workspace')

@section('content')

  {{-- Icon --}}
  <div style="text-align:center;margin-bottom:28px;">
    <div style="display:inline-block;background:#f5f3ff;border-radius:50%;width:56px;height:56px;line-height:56px;font-size:26px;">
      🎉
    </div>
  </div>

  {{-- Heading --}}
  <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0f172a;text-align:center;">
    You're invited!
  </h1>
  <p style="margin:0 0 24px;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
    You've been invited to join
    @if(!empty($tenantName))
      the <strong style="color:#0f172a;">{{ $tenantName }}</strong> workspace
    @else
      a workspace
    @endif
    on Tenant Workspace.
  </p>

  {{-- Info card --}}
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:28px;">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;padding-bottom:4px;">
          Workspace
        </td>
      </tr>
      <tr>
        <td style="font-size:14px;color:#0f172a;font-weight:600;">
          {{ $tenantName ?? 'Tenant Workspace' }}
        </td>
      </tr>
    </table>
  </div>

  {{-- CTA Button --}}
  <div style="text-align:center;margin-bottom:28px;">
    <a href="{{ $url }}"
       style="display:inline-block;padding:13px 32px;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;letter-spacing:.01em;">
      Accept Invitation
    </a>
  </div>

  {{-- Fallback URL --}}
  <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0 0 24px;line-height:1.6;">
    Or copy this link into your browser:<br />
    <a href="{{ $url }}" style="color:#7c3aed;word-break:break-all;">{{ $url }}</a>
  </p>

  {{-- Divider --}}
  <hr style="border:none;border-top:1px solid #f1f5f9;margin:0 0 20px;" />

  {{-- Footer note --}}
  <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0;line-height:1.6;">
    This invitation expires in <strong>48 hours</strong>. If you weren't expecting this, you can safely ignore it.
  </p>

@endsection
