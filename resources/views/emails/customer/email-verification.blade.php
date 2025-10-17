@component('mail::message')
# Welcome to Your Customer Portal!

Dear {{ $customer->name ?? 'Valued Customer' }},

Welcome to the **{{ company_name() }}** Customer Portal! We're excited to have you join our family of satisfied customers.

To complete your account setup and ensure secure access to your insurance information, please verify your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Verify Email Address
@endcomponent

**What you'll get access to after verification:**
- View your insurance policies and coverage details
- Track policy renewals and payment schedules  
- Access important documents and certificates
- Receive personalized insurance recommendations
- Contact our support team directly through the portal

**Security Note:** This verification link is valid for 24 hours and can only be used once.

If you're having trouble with the button above, you can also copy and paste the following link into your web browser:

{{ $verificationUrl }}

---

**Questions?** We're here to help with your insurance needs and account setup.

Best regards,<br>
**{{ company_advisor_name() }}**<br>
Insurance Advisor<br>
{{ company_name() }}

@slot('subcopy')
If you did not create an account with us, please ignore this email. No further action is required, and your email address will not be added to our system.
@endslot
@endcomponent
