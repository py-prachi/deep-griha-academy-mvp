<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DGA Admission Form</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9.5pt;
    color: #000;
    background: #fff;
  }

  .page { padding: 14px 18px; }

  /* ── OUTER FORM BORDER ── */
  .form-outer {
    border: 2px solid #000;
    width: 100%;
  }

  /* ── HEADER BLOCK ── */
  .header-block {
    border-bottom: 2px solid #000;
    padding: 0;
  }
  .header-inner {
    width: 100%;
    border-collapse: collapse;
  }
  .header-inner td {
    padding: 6px 8px;
    vertical-align: middle;
  }
  .logo-cell { width: 75px; text-align: center; border-right: 1px solid #000; }
  .logo-cell img { width: 65px; height: 65px; }
  .title-cell { text-align: center; vertical-align: middle; }
  .society-name { font-size: 8pt; color: #444; }
  .tax-line { font-size: 7.5pt; color: #666; }
  .school-name-big { font-size: 15pt; font-weight: bold; letter-spacing: 0.5px; }
  .school-address { font-size: 7.5pt; color: #444; margin-top: 1px; }
  .form-title-box {
    border-top: 1.5px solid #000;
    border-bottom: 1.5px solid #000;
    margin-top: 4px;
    padding: 2px 0;
    font-size: 12pt;
    font-weight: bold;
    letter-spacing: 1px;
  }
  .class-subtitle { font-size: 8.5pt; margin-top: 2px; color: #222; }
  .photo-cell { width: 95px; text-align: center; border-left: 1px solid #000; vertical-align: top; padding: 6px 6px 0 6px; }
  .photo-box {
    border: 1px solid #000;
    width: 75px;
    height: 90px;
    text-align: center;
    font-size: 7.5pt;
    color: #555;
    padding-top: 35px;
    margin: 0 auto;
  }
  .form-no { font-size: 7.5pt; margin-top: 4px; }
  .form-no-line { border-bottom: 1px solid #000; display: block; height: 12px; }
  .ay-line { font-size: 7.5pt; margin-top: 4px; }

  /* ── FIELD ROWS ── */
  .form-row {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1px solid #000;
  }
  .form-row td {
    padding: 3px 6px 2px 6px;
    vertical-align: bottom;
    border-right: 1px solid #000;
  }
  .form-row td:last-child { border-right: none; }

  .lbl {
    font-size: 7.8pt;
    color: #333;
    display: block;
    margin-bottom: 1px;
  }
  .val {
    border-bottom: 1px solid #555;
    display: block;
    min-height: 13px;
  }
  .val-tall {
    border-bottom: 1px solid #555;
    display: block;
    min-height: 20px;
  }

  /* checkbox */
  .cb { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; margin-right: 2px; vertical-align: middle; }

  /* last row — no bottom border */
  .form-row-last {
    width: 100%;
    border-collapse: collapse;
  }
  .form-row-last td {
    padding: 3px 6px 2px 6px;
    vertical-align: bottom;
    border-right: 1px solid #000;
  }
  .form-row-last td:last-child { border-right: none; }

  /* section label row */
  .section-row {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1px solid #000;
    background: #f0f0f0;
  }
  .section-row td {
    padding: 2px 6px;
    font-size: 8.5pt;
    font-weight: bold;
    letter-spacing: 0.3px;
  }

  /* docs checklist */
  .docs-cell { font-size: 8.5pt; padding: 4px 8px; line-height: 1.7; }

  /* declaration */
  .decl-cell { font-size: 7.8pt; color: #333; line-height: 1.45; padding: 4px 8px; vertical-align: top; }

  /* office use */
  .office-header {
    background: #000;
    color: #fff;
    font-size: 8.5pt;
    font-weight: bold;
    text-align: center;
    padding: 3px 6px;
    border-top: 2px solid #000;
    letter-spacing: 1px;
  }
</style>
</head>
<body>
<div class="page">
<div class="form-outer">

  {{-- ══════════════════════ HEADER ══════════════════════ --}}
  <div class="header-block">
    <table class="header-inner">
      <tr>
        <td class="logo-cell">
          @if($logoData)
            <img src="{{ $logoData }}" alt="DGA">
          @endif
        </td>
        <td class="title-cell">
          <div class="society-name">Deep Griha Society's</div>
          <div class="tax-line">(Income Tax Exemption P/o (I.T. – F 888))</div>
          <div class="school-name-big">Deep Griha Academy</div>
          <div class="school-address">Dattgaon Chola, Chinchol, Taluka Daund, District Pune</div>
          <div class="school-address">deepgrihaacademy@gmail.com</div>
          <div class="form-title-box">ADMISSION FORM</div>
          <div class="class-subtitle">Nursery &nbsp;/&nbsp; Lower KG &nbsp;/&nbsp; Upper KG &nbsp;/&nbsp; Primary &nbsp;/&nbsp; Secondary Std.</div>
        </td>
        <td class="photo-cell">
          <div class="photo-box">Affix<br>Photo<br>Here</div>
          <div class="form-no">Form No.<br><span class="form-no-line"></span></div>
          <div class="ay-line">Academic Year<br>
            <strong style="font-size:9pt;">{{ $academicYear }}</strong>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ══════════════════════ ROW 1: Name + DOB + Gender + Caste ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:50%">
        <span class="lbl">Full Name of Student *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:18%">
        <span class="lbl">Date of Birth</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:16%">
        <span class="lbl">Gender</span>
        <span class="val"><span class="cb"></span>Male &nbsp;<span class="cb"></span>Female</span>
      </td>
      <td style="width:16%">
        <span class="lbl">Caste</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 2: Aadhaar + PEN ID + Blood Group ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:40%">
        <span class="lbl">Aadhaar No. (Child — 12 digits)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:30%">
        <span class="lbl">PEN ID (Permanent Education Number)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:15%">
        <span class="lbl">Blood Group</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:15%">
        <span class="lbl">Religion</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 3: Nationality + Place of Birth + Language ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:22%">
        <span class="lbl">Nationality</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:28%">
        <span class="lbl">Place of Birth</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:50%">
        <span class="lbl">Language Spoken at Home</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 4: Class + Previous School ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:35%">
        <span class="lbl">Class Applying For *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:65%">
        <span class="lbl">Previous School Last Attended (if any)</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ SECTION: FAMILY ══════════════════════ --}}
  <table class="section-row">
    <tr><td>FAMILY INFORMATION</td></tr>
  </table>

  {{-- ══════════════════════ ROW 5: Father Name + Occupation ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:50%">
        <span class="lbl">Father's Full Name</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:50%">
        <span class="lbl">Father's Occupation</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 6: Father Phone + Aadhaar ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:35%">
        <span class="lbl">Father's Mobile / Phone *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:35%">
        <span class="lbl">Father's Aadhaar No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:30%">
        <span class="lbl">Father's Email (if any)</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 7: Mother Name + Occupation ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:50%">
        <span class="lbl">Mother's Full Name</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:50%">
        <span class="lbl">Mother's Occupation</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 8: Mother Phone + Aadhaar ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:35%">
        <span class="lbl">Mother's Mobile / Phone</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:35%">
        <span class="lbl">Mother's Aadhaar No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:30%">
        <span class="lbl">Mother's Email (if any)</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 9: Sibling ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:55%">
        <span class="lbl">Sibling Name &amp; Age (if studying at DGA, mention class)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:45%">
        <span class="lbl">Guardian Name &amp; Occupation (if different from parents)</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ SECTION: ADDRESS ══════════════════════ --}}
  <table class="section-row">
    <tr><td>ADDRESS &amp; CONTACT</td></tr>
  </table>

  {{-- ══════════════════════ ROW 10: Full Address ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td>
        <span class="lbl">Full Home Address</span>
        <span class="val-tall">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 11: Village + City + PIN ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:33%">
        <span class="lbl">Village / Area</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:33%">
        <span class="lbl">City / Town *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:34%">
        <span class="lbl">PIN Code</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ ROW 12: Contact + Emergency + Distance + Transport ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:28%">
        <span class="lbl">Contact No. (Residence)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:28%">
        <span class="lbl">In Case of Emergency — Cell</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:22%">
        <span class="lbl">Distance from School</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:22%">
        <span class="lbl">Transport Required?</span>
        <span class="val"><span class="cb"></span>Yes &nbsp;<span class="cb"></span>No</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ SECTION: MEDICAL ══════════════════════ --}}
  <table class="section-row">
    <tr><td>MEDICAL INFORMATION</td></tr>
  </table>

  {{-- ══════════════════════ ROW 13: Allergies + Doctor ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td style="width:60%">
        <span class="lbl">Any Allergies / Medical Conditions (if any)</span>
        <span class="val-tall">&nbsp;</span>
      </td>
      <td style="width:40%">
        <span class="lbl">Doctor's Name &amp; Phone No.</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ SECTION: DOCUMENTS ══════════════════════ --}}
  <table class="section-row">
    <tr><td>DOCUMENTS TO BE SUBMITTED (please tick whichever is applicable)</td></tr>
  </table>

  <table class="form-row">
    <tr>
      <td class="docs-cell" style="width:50%">
        <span class="cb"></span> Birth Certificate &nbsp;&nbsp;
        <span class="cb"></span> Aadhaar Card (Child)<br>
        <span class="cb"></span> Aadhaar Card (Father) &nbsp;&nbsp;
        <span class="cb"></span> Aadhaar Card (Mother)<br>
        <span class="cb"></span> Passport Size Photos (4 copies)
      </td>
      <td class="docs-cell" style="width:50%">
        <span class="cb"></span> Transfer / Leaving Certificate from previous school<br>
        <span class="cb"></span> Caste Certificate (if applicable)<br>
        <span class="cb"></span> RTE Documents (if applicable) &nbsp;&nbsp;
        <span class="cb"></span> Previous Year Report Card
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ DECLARATION + SIGNATURE ══════════════════════ --}}
  <table class="form-row">
    <tr>
      <td class="decl-cell" style="width:68%">
        I / We, the parent(s) / guardian(s), hereby declare that the information provided above is true and correct to the best of my / our knowledge. I / We agree to abide by the rules and regulations of Deep Griha Academy. I / We understand that providing false information may result in cancellation of admission.
        <br><br>
        <strong>Date: </strong><span style="border-bottom:1px solid #000; display:inline-block; width:100px;">&nbsp;</span>
      </td>
      <td style="width:32%; text-align:center; vertical-align:bottom; padding:6px;">
        <span style="border-bottom:1px solid #000; display:block; min-height:40px;">&nbsp;</span>
        <span style="font-size:8pt;">Signature of Parent / Guardian</span>
      </td>
    </tr>
  </table>

  {{-- ══════════════════════ FOR OFFICE USE ══════════════════════ --}}
  <div class="office-header">FOR OFFICE USE ONLY</div>
  <table class="form-row-last">
    <tr>
      <td style="width:40%; border-top:1px solid #000; padding:3px 6px;">
        <span class="lbl">Admission Granted to Class</span>
        <span style="font-size:8pt;">
          <span class="cb"></span> Nursery &nbsp;
          <span class="cb"></span> LKG &nbsp;
          <span class="cb"></span> UKG &nbsp;
          <span class="cb"></span> Std.__
        </span>
      </td>
      <td style="width:20%; border-top:1px solid #000; padding:3px 6px;">
        <span class="lbl">Fee Category</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:20%; border-top:1px solid #000; padding:3px 6px;">
        <span class="lbl">Receipt No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:20%; border-top:1px solid #000; padding:3px 6px;">
        <span class="lbl">Date</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="border-top:1px solid #000; padding:3px 6px;">
        <span class="lbl">General Register ID / DGA Admission No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td colspan="2" style="border-top:1px solid #000; padding:3px 6px; text-align:center; vertical-align:bottom;">
        <span style="border-bottom:1px solid #000; display:block; min-height:28px;">&nbsp;</span>
        <span style="font-size:8pt;">Manager's Signature</span>
      </td>
    </tr>
  </table>

</div>
</div>
</body>
</html>
