<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DGA Admission Inquiry Form</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; color: #1a1a1a; background: #fff; }

  .page { padding: 18px 24px 18px 24px; }

  /* ── HEADER ── */
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  .header-logo-cell { width: 80px; vertical-align: middle; }
  .header-logo-cell img { width: 72px; height: 72px; }
  .header-text-cell { vertical-align: middle; text-align: center; }
  .school-name { font-size: 16pt; font-weight: bold; color: #1B3A6B; letter-spacing: 0.5px; }
  .school-sub { font-size: 9pt; color: #555; margin-top: 2px; }
  .form-title { font-size: 13pt; font-weight: bold; color: #1B3A6B; margin-top: 6px; }
  .form-title-bar { border-top: 2.5px solid #1B3A6B; border-bottom: 2.5px solid #1B3A6B; padding: 3px 0; margin-top: 4px; }
  .header-right-cell { width: 110px; vertical-align: middle; text-align: right; font-size: 8.5pt; color: #444; }

  /* ── DIVIDER ── */
  .divider { border: none; border-top: 1px solid #bbb; margin: 6px 0; }
  .gold-bar { border: none; border-top: 3px solid #F2C067; margin: 0; }

  /* ── SECTION HEADERS ── */
  .section-header {
    background-color: #1B3A6B;
    color: #fff;
    font-size: 9pt;
    font-weight: bold;
    padding: 4px 8px;
    margin: 8px 0 4px 0;
    letter-spacing: 0.3px;
  }

  /* ── FIELD ROWS ── */
  .fields-table { width: 100%; border-collapse: collapse; }
  .fields-table td { padding: 2px 4px; vertical-align: bottom; font-size: 9pt; }
  .field-label { font-size: 8pt; color: #444; white-space: nowrap; padding-right: 2px; }
  .field-line { border-bottom: 1px solid #333; min-height: 14px; display: block; }

  /* ── CHECKBOX ROW ── */
  .cb-row { font-size: 9pt; }
  .cb-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #333; margin-right: 3px; vertical-align: middle; }

  /* ── OFFICE USE BOX ── */
  .office-box { border: 1.5px solid #1B3A6B; padding: 6px 8px; margin-top: 10px; }
  .office-box-title { font-weight: bold; font-size: 9pt; color: #1B3A6B; margin-bottom: 4px; }
  .office-inner { width: 100%; border-collapse: collapse; }
  .office-inner td { padding: 2px 4px; font-size: 9pt; vertical-align: bottom; }

  /* ── DECLARATION ── */
  .declaration { font-size: 8pt; color: #555; margin-top: 8px; line-height: 1.4; }
  .sig-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  .sig-table td { padding: 2px 4px; font-size: 9pt; vertical-align: bottom; }

  /* ── FOOTER ── */
  .footer { margin-top: 10px; border-top: 1px solid #bbb; padding-top: 4px; font-size: 7.5pt; color: #888; text-align: center; }
</style>
</head>
<body>
<div class="page">

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- HEADER --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <table class="header-table">
    <tr>
      <td class="header-logo-cell">
        @if($logoData)
          <img src="{{ $logoData }}" alt="DGA Logo">
        @endif
      </td>
      <td class="header-text-cell">
        <div class="school-name">Deep Griha Academy</div>
        <div class="school-sub">In Teaching We Learn</div>
        <div class="form-title-bar">
          <div class="form-title">ADMISSION INQUIRY FORM</div>
        </div>
      </td>
      <td class="header-right-cell">
        Academic Year<br>
        <span style="font-weight:bold; font-size:10pt;">{{ $academicYear }}</span><br><br>
        Inquiry No. (office)<br>
        <span style="border-bottom:1px solid #333; display:block; min-height:14px; min-width:80px;"></span>
      </td>
    </tr>
  </table>
  <div class="gold-bar"></div>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- SECTION 1: STUDENT INFORMATION --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="section-header">1. STUDENT INFORMATION</div>
  <table class="fields-table">
    <tr>
      <td style="width:55%">
        <span class="field-label">Full Name of Student *</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:22%">
        <span class="field-label">Date of Birth</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:23%">
        <span class="field-label">Gender</span>
        <span class="field-line">
          <span class="cb-box"></span>Male &nbsp;
          <span class="cb-box"></span>Female
        </span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Aadhaar No. (12 digits)</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">PEN ID</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Blood Group</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Caste</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Religion</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Nationality</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Place of Birth</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Language Spoken at Home</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Class Applying For *</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <span class="field-label">Previous School Last Attended</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- SECTION 2: FAMILY INFORMATION --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="section-header">2. FAMILY INFORMATION</div>
  <table class="fields-table">
    <tr>
      <td style="width:50%">
        <span class="field-label">Father's Full Name</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:50%">
        <span class="field-label">Father's Occupation</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Father's Phone *</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Father's Aadhaar No.</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Mother's Full Name</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Mother's Occupation</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Mother's Phone</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Mother's Aadhaar No.</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Guardian Name (if different from parents)</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Guardian Occupation</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Guardian Address</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Sibling Name &amp; Age (if studying at DGA)</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- SECTION 3: ADDRESS & CONTACT --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="section-header">3. ADDRESS &amp; CONTACT</div>
  <table class="fields-table">
    <tr>
      <td colspan="3">
        <span class="field-label">Full Home Address</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td style="width:33%">
        <span class="field-label">Village / Area</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:33%">
        <span class="field-label">City *</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:34%">
        <span class="field-label">PIN Code</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="field-label">Distance from School</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Emergency Contact</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td>
        <span class="field-label">Transport Required?</span>
        <span class="field-line">
          <span class="cb-box"></span>Yes &nbsp;
          <span class="cb-box"></span>No
        </span>
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- SECTION 4: MEDICAL INFORMATION --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="section-header">4. MEDICAL INFORMATION</div>
  <table class="fields-table">
    <tr>
      <td style="width:60%">
        <span class="field-label">Allergies / Medical Conditions (if any)</span>
        <span class="field-line">&nbsp;</span>
        <span class="field-line" style="margin-top:2px;">&nbsp;</span>
      </td>
      <td style="width:40%">
        <span class="field-label">Doctor's Name &amp; Phone</span>
        <span class="field-line">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- SECTION 5: DOCUMENTS CHECKLIST --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="section-header">5. DOCUMENTS TO BE SUBMITTED</div>
  <table class="fields-table">
    <tr>
      <td style="width:50%; font-size:9pt; padding:1px 4px;">
        <span class="cb-box"></span> Birth Certificate<br>
        <span class="cb-box"></span> Aadhaar Card (Child)<br>
        <span class="cb-box"></span> Aadhaar Card (Parents)<br>
        <span class="cb-box"></span> Passport Size Photos (x4)
      </td>
      <td style="width:50%; font-size:9pt; padding:1px 4px;">
        <span class="cb-box"></span> Transfer / Leaving Certificate (if applicable)<br>
        <span class="cb-box"></span> Caste Certificate (if applicable)<br>
        <span class="cb-box"></span> RTE Documents (if applicable)<br>
        <span class="cb-box"></span> Previous Year Report Card
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- DECLARATION + SIGNATURES --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="declaration">
    I / We, the parent(s) / guardian(s), hereby declare that the information provided above is true and correct to the best of my/our knowledge. I/We agree to abide by the rules and regulations of Deep Griha Academy.
  </div>
  <table class="sig-table">
    <tr>
      <td style="width:40%">
        <span class="field-label">Date of Inquiry</span>
        <span class="field-line">&nbsp;</span>
      </td>
      <td style="width:30%">&nbsp;</td>
      <td style="width:30%; text-align:center;">
        <span style="border-bottom:1px solid #333; display:block; min-height:28px;">&nbsp;</span>
        <span style="font-size:8pt; color:#555;">Signature of Parent / Guardian</span>
      </td>
    </tr>
  </table>

  {{-- ═══════════════════════════════════════════════════════════════ --}}
  {{-- FOR OFFICE USE --}}
  {{-- ═══════════════════════════════════════════════════════════════ --}}
  <div class="office-box">
    <div class="office-box-title">FOR OFFICE USE ONLY</div>
    <table class="office-inner">
      <tr>
        <td style="width:28%">
          <span class="field-label">Inquiry No.</span>
          <span class="field-line">&nbsp;</span>
        </td>
        <td style="width:28%">
          <span class="field-label">Date Received</span>
          <span class="field-line">&nbsp;</span>
        </td>
        <td style="width:22%">
          <span class="field-label">Fee Category</span>
          <span class="field-line">&nbsp;</span>
        </td>
        <td style="width:22%">
          <span class="field-label">Received By</span>
          <span class="field-line">&nbsp;</span>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <span class="field-label">General Register ID / DGA Admission No.</span>
          <span class="field-line">&nbsp;</span>
        </td>
        <td colspan="2">
          <span class="field-label">Remarks</span>
          <span class="field-line">&nbsp;</span>
        </td>
      </tr>
    </table>
  </div>

  <div class="footer">
    Deep Griha Academy &nbsp;|&nbsp; In Teaching We Learn &nbsp;|&nbsp; Form generated {{ \Carbon\Carbon::now()->format('d M Y') }}
  </div>

</div>
</body>
</html>
