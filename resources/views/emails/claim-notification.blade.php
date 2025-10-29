<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Notification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .claim-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .claim-info h3 {
            margin: 0 0 15px 0;
            color: #007bff;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
        }
        .content {
            margin: 20px 0;
            line-height: 1.8;
        }
        .stage-update {
            background-color: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .stage-update h4 {
            margin: 0 0 10px 0;
            color: #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #777;
            font-size: 14px;
        }
        .contact-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .document-list {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .document-list h4 {
            margin: 0 0 15px 0;
            color: #856404;
        }
        .document-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .document-list li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ company_name() }}</div>
            <p>{{ company_title() }}</p>
        </div>

        <div class="content">
            <p>Dear {{ $claim->customer->name }},</p>

            @if($notificationType == 'claim_created')
                <p>We are pleased to confirm that your insurance claim has been successfully registered in our system.</p>

                <div class="claim-info">
                    <h3>Claim Registration Details</h3>
                    <div class="info-row">
                        <span class="label">Claim Number:</span>
                        <span class="value">{{ $claim->claim_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Insurance Type:</span>
                        <span class="value">{{ $claim->insurance_type }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Incident Date:</span>
                        <span class="value">{{ $claim->incident_date ? format_app_date($claim->incident_date) : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Policy Number:</span>
                        <span class="value">{{ $claim->customerInsurance->policy_no ?? 'N/A' }}</span>
                    </div>
                    @if($claim->customerInsurance->registration_no)
                    <div class="info-row">
                        <span class="label">Registration Number:</span>
                        <span class="value">{{ $claim->customerInsurance->registration_no }}</span>
                    </div>
                    @endif
                </div>

                <p>Our team will begin processing your claim immediately. You will receive updates via email and WhatsApp as your claim progresses through different stages.</p>

            @elseif($notificationType == 'stage_update')
                <p>We are writing to inform you about an update in your claim status.</p>

                <div class="stage-update">
                    <h4>Status Update</h4>
                    <p><strong>New Status:</strong> {{ $additionalData['stage_name'] ?? 'Status Updated' }}</p>
                    @if(isset($additionalData['description']) && $additionalData['description'])
                        <p><strong>Description:</strong> {{ $additionalData['description'] }}</p>
                    @endif
                    @if(isset($additionalData['notes']) && $additionalData['notes'])
                        <p><strong>Notes:</strong> {{ $additionalData['notes'] }}</p>
                    @endif
                </div>

            @elseif($notificationType == 'claim_number_assigned')
                <p>Great news! Your claim has been assigned an official claim number by the insurance company.</p>

                <div class="claim-info">
                    <h3>Official Claim Number</h3>
                    <div class="info-row">
                        <span class="label">Claim Number:</span>
                        <span class="value"><strong>{{ $claim->claim_number }}</strong></span>
                    </div>
                    @if($claim->customerInsurance->registration_no)
                    <div class="info-row">
                        <span class="label">Vehicle Number:</span>
                        <span class="value">{{ $claim->customerInsurance->registration_no }}</span>
                    </div>
                    @endif
                </div>

                <p>Please keep this claim number for all future reference and correspondence regarding your claim.</p>

            @elseif($notificationType == 'document_request')
                <p>To proceed with your claim processing, we require some additional documents from you.</p>

                @if(isset($additionalData['pending_documents']) && count($additionalData['pending_documents']) > 0)
                    <div class="document-list">
                        <h4>Required Documents</h4>
                        <ul>
                            @foreach($additionalData['pending_documents'] as $document)
                                <li>{{ $document['name'] }}
                                    @if($document['description'])
                                        <br><small style="color: #666;">{{ $document['description'] }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p>Please submit these documents at your earliest convenience to avoid any delays in claim processing.</p>

            @elseif($notificationType == 'claim_closed')
                <p>We are pleased to inform you that your insurance claim has been successfully processed and closed.</p>

                <div class="claim-info">
                    <h3>Claim Closure Summary</h3>
                    <div class="info-row">
                        <span class="label">Final Status:</span>
                        <span class="value">Closed</span>
                    </div>
                    @if(isset($additionalData['closure_reason']))
                    <div class="info-row">
                        <span class="label">Closure Reason:</span>
                        <span class="value">{{ $additionalData['closure_reason'] }}</span>
                    </div>
                    @endif
                    @if($claim->liabilityDetail && $claim->liabilityDetail->final_amount)
                    <div class="info-row">
                        <span class="label">Settlement Amount:</span>
                        <span class="value">₹{{ number_format($claim->liabilityDetail->final_amount, 2) }}</span>
                    </div>
                    @endif
                </div>

                <p>Thank you for choosing our insurance services. We appreciate your trust and business.</p>
            @endif

            <div class="claim-info">
                <h3>Your Claim Details</h3>
                <div class="info-row">
                    <span class="label">Claim Number:</span>
                    <span class="value">{{ $claim->claim_number }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Insurance Type:</span>
                    <span class="value">{{ $claim->insurance_type }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Current Status:</span>
                    <span class="value">{{ $claim->currentStage->stage_name ?? 'Processing' }}</span>
                </div>
            </div>

            <p>If you have any questions or need further assistance regarding your claim, please don't hesitate to contact us.</p>
        </div>

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p><strong>{{ company_advisor_name() }}</strong><br>
            {{ company_title() }}<br>
            Phone: {{ company_phone() }}<br>
            Website: <a href="{{ company_website() }}">{{ company_website() }}</a></p>
            <p style="font-style: italic; margin-top: 15px;">
                "{{ company_tagline() }}"
            </p>
        </div>

        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>
                @if(show_footer_year())
                    {{ footer_copyright_text() }} - {{ date('Y') }}
                @else
                    {{ footer_copyright_text() }}
                @endif
                @if(show_footer_developer())
                    | Developed by <a href="{{ footer_developer_url() }}" style="color: #007bff; text-decoration: none;">{{ footer_developer_name() }}</a>
                @endif
            </p>
        </div>
    </div>
</body>
</html>