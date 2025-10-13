@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="{{ company_logo_asset() }}" class="logo" alt="{{ company_logo('alt') }}"
                style="height: {{ app_setting('company_email_logo_height', 'branding', '60px') }}; width: auto;">
        </a>
        <div class="company-info" style="text-align: center; margin-top: 10px;">
            <h2 style="color: #20b2aa; margin: 0; font-size: 18px; font-weight: bold; font-color: #FFFFFF;">{{ company_title() }}</h2>
            <p style="color: #666; margin: 5px 0 0 0; font-size: 14px; font-color: #FFFFFF;">{{ company_tagline() }}</p>
        </div>
    </td>
</tr>
