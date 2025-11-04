@component('mail::message')
# New Contact Form Submission

You have received a new contact form submission from your website.

## Submission Details

**Name:** {{ $submission->name }}
**Email:** {{ $submission->email }}
**Phone:** {{ $submission->phone ?? 'Not provided' }}
**Company:** {{ $submission->company ?? 'Not provided' }}

**Message:**
{{ $submission->message }}

---

**Submitted On:** {{ $submission->created_at->format('F d, Y \a\t g:i A') }}
**IP Address:** {{ $submission->ip_address }}

@component('mail::button', ['url' => route('central.contact-submissions.show', $submission)])
View in Admin Panel
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
