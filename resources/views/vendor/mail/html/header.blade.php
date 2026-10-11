@props(['url'])
@php
    $appUrl = (string) config('app.url');
    $isLocal = preg_match('/localhost|127\.0\.0\.1|\.test|\.local/i', $appUrl) === 1;
@endphp
<tr>
<td class="header" style="padding: 0 0 20px 0;">
<table align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="width: 570px; max-width: 100%; background-color: #14532d; background-image: linear-gradient(135deg, #052e16 0%, #14532d 100%);">
<tr>
<td align="center" style="padding: 22px 24px;">
<a href="{{ route('landing') }}" target="_blank" style="display: inline-block; text-decoration: none;">
<table align="center" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td valign="middle" style="padding-right: 14px;">
</td>
<td valign="middle" align="center" style="text-align: center;">
<div style="font-size: 22px; font-weight: 700; line-height: 1.2; color: #ffffff; font-family: Arial, Helvetica, sans-serif;">AgriStock</div>
<div style="font-size: 13px; font-weight: 500; line-height: 1.5; color: #86efac; font-family: Arial, Helvetica, sans-serif;">Farm Inventory System
</div>
</td>
</tr>
</table>
</a>
</td>
</tr>
</table>
</td>
</tr>
