# WhatsApp Lead Marketing System - User Guide

## Table of Contents
1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Sending Individual Messages](#sending-individual-messages)
4. [Sending Bulk Messages](#sending-bulk-messages)
5. [Managing Templates](#managing-templates)
6. [Campaign Management](#campaign-management)
7. [Analytics & Reporting](#analytics--reporting)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The WhatsApp Lead Marketing System allows you to communicate with leads directly through WhatsApp, helping you:
- Send personalized messages to individual leads
- Execute bulk messaging campaigns
- Track message delivery and read status
- Manage reusable message templates
- Analyze campaign performance

**Key Features:**
- ✅ Individual & bulk messaging
- ✅ File attachment support (PDF, images, documents)
- ✅ Template library with variable replacement
- ✅ Campaign scheduling and automation
- ✅ Real-time delivery tracking
- ✅ Comprehensive analytics dashboard

---

## Getting Started

### Prerequisites
- User account with WhatsApp permissions
- Leads with valid mobile numbers in the system
- WhatsApp API configured (contact system administrator)

### Required Permissions
Your user role must have these permissions enabled:
- `lead-whatsapp-send` - Send WhatsApp messages
- `lead-whatsapp-campaign-list` - View campaigns
- `lead-whatsapp-campaign-create` - Create campaigns
- `lead-whatsapp-template-list` - View templates
- `lead-whatsapp-template-create` - Create templates

**To check your permissions:** Contact your system administrator or manager.

---

## Sending Individual Messages

### Method 1: From Lead Detail Page

1. **Navigate to Lead**
   - Go to **Leads → All Leads**
   - Click on any lead to view their details

2. **Send WhatsApp Message**
   - Click the **"Send WhatsApp"** button (green button with WhatsApp icon)
   - A modal will appear

3. **Compose Message**
   - **Template (Optional):** Select a pre-made template from dropdown
   - **Message:** Type your custom message (max 4,096 characters)
   - **Attachment (Optional):** Upload a file (PDF, JPG, PNG, DOC - max 5MB)
   - Review character count at bottom-right

4. **Send**
   - Click **"Send WhatsApp"** button
   - Wait for confirmation message
   - Message will appear in history below

### Available Variables
Use these placeholders in your messages - they'll be automatically replaced:
- `{name}` - Lead's full name
- `{mobile}` - Lead's mobile number
- `{email}` - Lead's email address
- `{source}` - How the lead was acquired
- `{status}` - Current lead status
- `{priority}` - Lead priority level
- `{assigned_to}` - Assigned sales person
- `{product_interest}` - Product/service of interest
- `{lead_number}` - Unique lead identifier

**Example:**
```
Hi {name},

Thank you for your interest in {product_interest}.
I'm {assigned_to} from your dedicated sales team.

Can we schedule a call to discuss your requirements?

Best regards,
Midas Insurance
```

### Viewing Message History

On any lead detail page, scroll down to the **"WhatsApp Messages"** section to see:
- All sent messages
- Delivery status (Pending/Sent/Delivered/Read/Failed)
- Sent by whom and when
- Attachments (if any)
- Error messages (if failed)

---

## Sending Bulk Messages

### Step 1: Select Leads

1. Go to **Leads → All Leads**
2. Use checkboxes to select multiple leads
   - Check individual leads OR
   - Click checkbox in header to select all visible leads
3. A **"Bulk Actions Bar"** appears at top showing count

### Step 2: Open Bulk WhatsApp Modal

1. Click **"Send WhatsApp"** button in bulk actions bar
2. The bulk messaging modal opens
3. You'll see: "Sending to X selected leads"

### Step 3: Compose Message

1. **Select Template (Optional):**
   - Choose from dropdown for pre-written messages
   - Template will populate message field

2. **Compose/Edit Message:**
   - Type or edit your message
   - Use variables like `{name}`, `{product_interest}`
   - Watch character counter (0 / 4096)

3. **Add Attachment (Optional):**
   - Click "Choose File"
   - Select PDF, image, or document (max 5MB)
   - One attachment per bulk send

### Step 4: Send Messages

1. Review message and lead count
2. Click **"Send WhatsApp Messages"**
3. System will:
   - **If ≤10 leads:** Send immediately
   - **If >10 leads:** Queue for background processing

4. You'll receive a confirmation message

### Important Notes:
- Variables are replaced with actual lead data for each recipient
- Messages are personalized automatically
- Failed sends are logged with error reasons
- You can retry failed messages from campaign details

---

## Managing Templates

Templates are reusable message formats that save time and ensure consistency.

### Creating a Template

1. **Navigate to Templates**
   - Sidebar: **Leads → WhatsApp Templates**
   - Or click **"Create Template"** button

2. **Fill Template Details**
   - **Name:** Descriptive name (e.g., "Welcome Message")
   - **Category:** Select from:
     - **Greeting:** Welcome/introduction messages
     - **Follow-up:** After initial contact
     - **Promotion:** Special offers/discounts
     - **Reminder:** Payment/meeting reminders
     - **General:** Other purposes

3. **Write Message Template**
   - Type your message in the text area
   - Insert variables using curly braces: `{name}`, `{mobile}`, etc.
   - Preview shows how it looks with sample data
   - Character counter helps stay within limit

4. **Add Attachment (Optional)**
   - Upload a default attachment for this template
   - Useful for brochures, price lists, etc.

5. **Save Template**
   - Click **"Create Template"**
   - Template is now available in all dropdowns

### Editing Templates

1. Go to **Leads → WhatsApp Templates**
2. Find template in list
3. Click **Edit** icon (pencil)
4. Modify details as needed
5. Toggle **"Active Template"** to enable/disable
6. View usage statistics at bottom
7. Click **"Update Template"**

### Deleting Templates

1. Find template in list
2. Click **Delete** icon (trash)
3. Confirm deletion
4. **Note:** Cannot be undone - used templates remain in message history

### Template Best Practices

✅ **DO:**
- Use clear, descriptive names
- Include all relevant variables
- Keep messages concise (aim for <500 characters)
- Test templates before using in campaigns
- Use proper grammar and formatting
- Include call-to-action (CTA)

❌ **DON'T:**
- Use ALL CAPS (looks spammy)
- Include excessive emojis
- Make promises you can't keep
- Use misleading subject matter
- Forget personalization variables

---

## Campaign Management

Campaigns allow you to send WhatsApp messages to targeted lead groups with automation and tracking.

### Creating a Campaign

1. **Navigate to Campaigns**
   - Sidebar: **Leads → WhatsApp Campaigns**
   - Click **"Create Campaign"**

2. **Step 1: Campaign Details**
   - **Name:** Descriptive campaign name
   - **Description:** Purpose and goals (optional)
   - **Status:** Draft or Scheduled
   - **Scheduled Date/Time:** If scheduling for later

3. **Step 2: Target Selection**
   - **Option A - Manual:** Select specific leads
   - **Option B - Criteria:** Filter by source, status, priority, etc.
   - Click **"Preview Target Leads"** to verify selection

4. **Step 3: Message Composition**
   - **Template (Optional):** Select existing template
   - **Message:** Compose or edit message
   - Use variables for personalization
   - **Attachment (Optional):** Upload file if needed

5. **Step 4: Advanced Settings**
   - **Messages Per Minute:** Control send rate (default: 100)
     - Lower = more reliable, slower
     - Higher = faster, may hit API limits
   - **Auto Retry Failed:** Enable automatic retry on failures
   - **Max Retry Attempts:** How many times to retry (default: 3)

6. **Save Campaign**
   - Click **"Create Campaign"**
   - Redirects to campaign details page

### Executing a Campaign

1. Go to **Leads → WhatsApp Campaigns**
2. Find campaign in list
3. Click campaign name to view details
4. Review:
   - Target lead count
   - Message preview
   - Settings
5. Click **"Execute Campaign"** button
6. Confirm execution

**What Happens:**
- **Small campaigns (<50 leads):** Execute immediately
- **Large campaigns (≥50 leads):** Queued for background processing
- Progress tracked in real-time
- Delivery status updated automatically

### Campaign Statuses

| Status | Description | Actions Available |
|--------|-------------|-------------------|
| **Draft** | Not yet ready | Edit, Execute, Delete |
| **Scheduled** | Set to run at specific time | Execute Now, Pause, Cancel |
| **Active** | Currently sending messages | Pause, View Progress |
| **Paused** | Temporarily stopped | Resume, Cancel |
| **Completed** | Finished sending | View Reports, Retry Failed |
| **Cancelled** | Stopped permanently | View Reports |

### Monitoring Campaign Progress

On campaign details page, you'll see:
- **Overview Cards:**
  - Total Leads
  - Sent Count
  - Delivered Count
  - Failed Count
  - Success Rate

- **Lead-by-Lead Status Table:**
  - Each lead's delivery status
  - Sent time
  - Error messages (if any)
  - Retry count

- **Actions:**
  - Pause/Resume campaign
  - Retry failed messages
  - Download report

### Retrying Failed Messages

1. Open campaign details
2. Scroll to **"Failed Messages"** section
3. Review error reasons
4. Click **"Retry Failed Messages"**
5. System will attempt redelivery with backoff delays:
   - 1st retry: After 30 seconds
   - 2nd retry: After 60 seconds
   - 3rd retry: After 120 seconds

---

## Analytics & Reporting

### Accessing Analytics Dashboard

- Sidebar: **Leads → WhatsApp Analytics**

### Overview Metrics

**Key Performance Indicators (KPIs):**
- **Total Messages:** All messages sent
- **Sent Successfully:** Messages accepted by WhatsApp API
- **Delivered:** Messages delivered to recipient's device
- **Failed:** Messages that couldn't be delivered
- **Delivery Rate:** (Delivered / Total) × 100
- **Failure Rate:** (Failed / Total) × 100

### Charts & Visualizations

1. **Message Delivery Trend (Line Chart)**
   - Shows sent vs delivered over time
   - Helps identify peak performance periods
   - Spot patterns and issues

2. **Status Distribution (Pie Chart)**
   - Breakdown by status: Sent, Delivered, Failed, Pending
   - Quick visual of overall health

3. **Recent Campaigns Table**
   - Last 5 campaigns
   - Quick stats for each
   - Links to campaign details

4. **Top Templates**
   - Most used templates
   - Usage count for each
   - Helps identify what works

### Filtering Analytics

Use date filters at top:
- **From Date:** Start of period
- **To Date:** End of period
- Click **"Apply Filter"**
- Click **"Reset"** to clear

### Exporting Reports

*(Future Enhancement)*
- PDF export of analytics
- CSV export of campaign data
- Scheduled email reports

---

## Best Practices

### Timing

**Best Times to Send:**
- ✅ **Weekdays 10 AM - 5 PM:** Business hours
- ✅ **Tuesday-Thursday:** Highest engagement
- ⚠️ **Avoid early mornings (<9 AM) and late evenings (>8 PM)**
- ❌ **Avoid Sundays and public holidays**

### Message Content

**DO:**
- Keep it short and scannable
- Lead with value proposition
- Include clear call-to-action
- Personalize with recipient's name
- Use proper grammar and spelling
- Respect professional tone

**DON'T:**
- Send unsolicited promotional content
- Use excessive formatting or emojis
- Include misleading information
- Forget to identify yourself/company
- Send without permission

### Compliance

**Legal Requirements:**
- Only message leads who consented
- Include opt-out instructions
- Respect "Do Not Contact" requests
- Follow local data protection laws (GDPR, etc.)
- Keep records of consent

**Example Opt-Out Language:**
```
Reply STOP to unsubscribe from marketing messages.
```

### Frequency

**Recommended:**
- Initial contact: Within 24 hours of lead creation
- Follow-ups: Every 3-5 days (max 3 attempts)
- Promotional: Once per week maximum
- Reminders: As needed for scheduled events

**Avoid:**
- Daily messages (appears spammy)
- Multiple messages in same day
- Continued messaging after "not interested"

### Rate Limiting

To avoid being flagged as spam:
- Use default **100 messages per minute**
- For high-value campaigns: Reduce to **50/min**
- Monitor delivery rates - if dropping, slow down
- Space campaigns 1-2 hours apart

---

## Troubleshooting

### Common Issues

#### 1. Message Stuck in "Pending" Status

**Possible Causes:**
- WhatsApp API unavailable
- Rate limit exceeded
- Invalid mobile number format

**Solutions:**
- Wait 5 minutes and check again
- Verify mobile number includes country code
- Check API status with administrator
- Retry sending from message history

#### 2. "Failed" Message Status

**Common Reasons:**
- Invalid/disconnected mobile number
- Recipient blocked business number
- Attachment too large (>5MB)
- Unsupported file format

**Solutions:**
- Verify mobile number is correct
- Check if lead's number is active
- Reduce attachment size or use link instead
- Use supported formats only (PDF, JPG, PNG, DOC)

#### 3. Template Not Appearing in Dropdown

**Causes:**
- Template is inactive
- Template was recently created (cache issue)
- No permission to view templates

**Solutions:**
- Check template status (edit template → ensure "Active" is checked)
- Refresh page (Ctrl+F5)
- Clear browser cache
- Contact administrator for permissions

#### 4. Campaign Not Executing

**Possible Issues:**
- Campaign status is "Draft" or "Paused"
- No leads match target criteria
- Queue worker not running (for large campaigns)

**Solutions:**
- Change status to "Scheduled" or "Active"
- Review target criteria and preview leads
- Contact administrator to check queue status
- Try with smaller lead count first

#### 5. Variables Not Replacing in Messages

**Causes:**
- Typo in variable name
- Lead missing required data
- Variables not in correct format

**Solutions:**
- Double-check spelling: `{name}` not `{Name}`
- Ensure curly braces are used: `{name}` not `[name]`
- Preview message before sending
- Check lead profile has data for that field

### Error Messages

| Error | Meaning | Solution |
|-------|---------|----------|
| "Mobile number required" | Lead has no phone number | Add mobile number to lead profile |
| "Invalid format" | Number format incorrect | Use format: +[country code][number] |
| "Rate limit exceeded" | Too many requests | Wait 60 seconds, reduce send rate |
| "Unauthorized" | No permission | Contact administrator |
| "Template not found" | Template deleted/inactive | Select different template |
| "File too large" | Attachment over 5MB | Compress file or use link |

### Getting Help

If you continue experiencing issues:

1. **Check System Status**
   - Verify internet connection
   - Check if other users experiencing same issue

2. **Review Logs**
   - Individual messages show error details
   - Campaign details include failure reasons

3. **Contact Support**
   - Email: support@midasportal.com
   - Include:
     - Screenshot of error
     - Lead ID or campaign ID
     - Steps to reproduce issue
     - Time when error occurred

4. **Administrator Tasks**
   - WhatsApp API configuration
   - Permission management
   - Queue worker monitoring
   - Database issues

---

## Appendix

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+Enter` | Send message (in modals) |
| `Esc` | Close modal |
| `Ctrl+F` | Search in page |

### Mobile Number Format

**Correct Formats:**
- `+919876543210` (India)
- `+14155551234` (US)
- `+447911123456` (UK)

**Include:**
- ✅ Country code with +
- ✅ No spaces or dashes
- ✅ Only numbers after +

**Avoid:**
- ❌ Leading zeros: `09876543210`
- ❌ Spaces: `+91 98765 43210`
- ❌ Dashes: `+91-9876-543210`
- ❌ Parentheses: `(91) 9876543210`

### Message Length Guidelines

- **SMS length:** 160 characters
- **Short message:** 160-300 characters (recommended)
- **Medium message:** 300-600 characters
- **Long message:** 600-1000 characters
- **Maximum:** 4096 characters

**Tip:** Shorter messages have higher engagement rates. Aim for 200-400 characters.

### Supported File Types

**Documents:**
- PDF (.pdf) - Up to 5MB
- Word (.doc, .docx) - Up to 5MB

**Images:**
- JPEG (.jpg, .jpeg) - Up to 5MB
- PNG (.png) - Up to 5MB

**Not Supported:**
- Excel files (.xls, .xlsx)
- PowerPoint (.ppt, .pptx)
- Zip archives (.zip, .rar)
- Executables (.exe, .dmg)
- Audio files (.mp3, .wav)
- Video files (.mp4, .avi)

---

## Glossary

| Term | Definition |
|------|------------|
| **Campaign** | Coordinated messaging effort to multiple leads |
| **Template** | Reusable message format with variables |
| **Variable** | Placeholder replaced with lead data (e.g., `{name}`) |
| **Delivery Status** | Current state of message (pending/sent/delivered/read/failed) |
| **Webhook** | Automated callback for status updates |
| **Rate Limit** | Maximum messages allowed per time period |
| **Retry** | Automatic resend attempt for failed messages |
| **Queue** | Background job processing system |
| **Throttling** | Controlling send speed to avoid limits |
| **Opt-out** | User request to stop receiving messages |

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Nov 2, 2025 | Initial release |

---

**For technical documentation, see:** `WHATSAPP_LEAD_IMPLEMENTATION.md`

**For API reference, see:** `API_REFERENCE.md`

---

*This guide is maintained by the Midas Portal development team. Last updated: November 2, 2025*
