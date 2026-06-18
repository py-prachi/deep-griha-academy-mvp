<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DGA Admission Form</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10pt;
    color: #000;
    background: #fff;
  }

  /* ── PAGE BREAK ── */
  .page-break { page-break-after: always; }

  /* ── OUTER BORDER ── */
  .form-outer { border: 2px solid #000; width: 100%; border-collapse: collapse; }

  /* ── HEADER ── */
  .header-wrap { border-bottom: 2px solid #000; padding: 8px 10px 6px 10px; }
  .header-tbl { width: 100%; border-collapse: collapse; }
  .header-tbl td { vertical-align: middle; padding: 0; }
  .hdr-left { width: 75%; vertical-align: middle; }
  .hdr-right { width: 25%; vertical-align: top; text-align: center; padding-left: 8px; }

  .logo-img { width: 180px; height: auto; display: block; margin-bottom: 4px; }
  .society-name { font-size: 8pt; color: #444; }
  .tax-line { font-size: 7.5pt; color: #666; margin-bottom: 2px; }
  .academy-name { font-size: 14pt; font-weight: bold; letter-spacing: 0.3px; margin: 1px 0; }
  .address-line { font-size: 8pt; color: #333; }

  .form-title-bar {
    border-top: 1.5px solid #000;
    border-bottom: 1.5px solid #000;
    margin-top: 5px;
    padding: 3px 0;
    text-align: center;
    font-size: 13pt;
    font-weight: bold;
    letter-spacing: 2px;
  }
  .class-line { font-size: 8.5pt; text-align: center; margin-top: 3px; color: #222; }

  .photo-box {
    border: 1px solid #000;
    width: 85px;
    height: 105px;
    margin: 0 auto 5px auto;
    display: block;
    text-align: center;
    font-size: 7.5pt;
    color: #555;
    padding-top: 40px;
  }
  .form-no-lbl { font-size: 7.5pt; color: #444; margin-top: 4px; }
  .form-no-line { border-bottom: 1px solid #000; display: block; height: 14px; margin-top: 2px; }
  .ay-lbl { font-size: 7.5pt; color: #444; margin-top: 5px; }
  .ay-val { font-size: 9pt; font-weight: bold; display: block; margin-top: 1px; }

  /* ── SECTION DIVIDER ── */
  .sec-div { background: #222; color: #fff; font-size: 9pt; font-weight: bold;
             padding: 4px 10px; letter-spacing: 0.8px; border-bottom: 1px solid #000; }

  /* ── FIELD ROWS — main content ── */
  .fr { width: 100%; border-collapse: collapse; border-bottom: 1px solid #000; }
  .fr td {
    padding: 4px 8px 3px 8px;
    vertical-align: bottom;
    border-right: 1px solid #000;
  }
  .fr td:last-child { border-right: none; }

  /* No-border-bottom version for last row in a section before page break */
  .fr-last { width: 100%; border-collapse: collapse; }
  .fr-last td {
    padding: 4px 8px 3px 8px;
    vertical-align: bottom;
    border-right: 1px solid #000;
  }
  .fr-last td:last-child { border-right: none; }

  .lbl { font-size: 7.8pt; color: #333; display: block; margin-bottom: 2px; }
  .val { border-bottom: 1.5px solid #333; display: block; min-height: 17px; }
  .val-tall { border-bottom: 1.5px solid #333; display: block; min-height: 28px; }
  .val-xl { border-bottom: 1.5px solid #333; display: block; min-height: 42px; }

  .cb { display: inline-block; width: 10px; height: 10px; border: 1px solid #000;
        margin-right: 2px; vertical-align: middle; }

  /* ── MINI HEADER (page 2) ── */
  .mini-header { border-bottom: 2px solid #000; padding: 6px 10px; }
  .mini-tbl { width: 100%; border-collapse: collapse; }
  .mini-tbl td { vertical-align: middle; }
  .mini-school { font-size: 11pt; font-weight: bold; }
  .mini-sub { font-size: 8pt; color: #444; }
  .mini-right { text-align: right; font-size: 8.5pt; }

  /* ── DOCS CHECKLIST ── */
  .docs-td { font-size: 9pt; line-height: 2.0; padding: 5px 10px; vertical-align: top; }

  /* ── DECLARATION ── */
  .decl-td { font-size: 8.5pt; color: #222; line-height: 1.55; padding: 6px 10px; vertical-align: top; }

  /* ── OFFICE USE ── */
  .office-hdr { background: #000; color: #fff; text-align: center; font-size: 9pt;
                font-weight: bold; padding: 4px 8px; letter-spacing: 1.5px; }
  .office-fr { width: 100%; border-collapse: collapse; }
  .office-fr td { padding: 5px 8px; vertical-align: bottom; border-right: 1px solid #000;
                  border-top: 1px solid #000; }
  .office-fr td:last-child { border-right: none; }
</style>
</head>
<body>

{{-- ╔══════════════════════════════════════════════════════════════╗ --}}
{{-- ║                        PAGE  1                              ║ --}}
{{-- ╚══════════════════════════════════════════════════════════════╝ --}}
<div class="page-break">
<div class="form-outer">

  {{-- ── HEADER ── --}}
  <div class="header-wrap">
    <table class="header-tbl">
      <tr>
        <td class="hdr-left">
          @if($logoData)
            <img class="logo-img" src="{{ $logoData }}" alt="Deep Griha Academy">
          @endif
          <div class="society-name">Deep Griha Society's</div>
          <div class="tax-line">(Income Tax Exemption P/o (I.T. – F 888))</div>
          <div class="academy-name">Deep Griha Academy</div>
          <div class="address-line">Dattgaon Chola, Chinchol, Taluka Daund, District Pune</div>
          <div class="address-line">deepgrihaacademy@gmail.com</div>
          <div class="form-title-bar">ADMISSION FORM</div>
          <div class="class-line">Nursery &nbsp;/&nbsp; Lower KG &nbsp;/&nbsp; Upper KG &nbsp;/&nbsp; Primary Std. &nbsp;/&nbsp; Secondary Std.</div>
        </td>
        <td class="hdr-right">
          <div class="photo-box">Affix<br>Photo<br>Here</div>
          <div class="form-no-lbl">Form No.</div>
          <span class="form-no-line"></span>
          <div class="ay-lbl">Academic Year</div>
          <span class="ay-val">{{ $academicYear }}</span>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── SECTION A: STUDENT INFORMATION ── --}}
  <div class="sec-div">A. STUDENT INFORMATION</div>

  <table class="fr">
    <tr>
      <td style="width:52%">
        <span class="lbl">Full Name of Student &nbsp;<span style="font-size:7pt;">(as per Birth Certificate)</span> *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:24%">
        <span class="lbl">Date of Birth (DD/MM/YYYY)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:24%">
        <span class="lbl">Gender</span>
        <span class="val"><span class="cb"></span> Male &nbsp;&nbsp; <span class="cb"></span> Female</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:38%">
        <span class="lbl">Aadhaar No. &nbsp;<span style="font-size:7pt;">(Child — 12 digits)</span></span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:32%">
        <span class="lbl">PEN ID &nbsp;<span style="font-size:7pt;">(Permanent Education Number)</span></span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:16%">
        <span class="lbl">Blood Group</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:14%">
        <span class="lbl">Caste</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:22%">
        <span class="lbl">Religion</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:22%">
        <span class="lbl">Nationality</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:28%">
        <span class="lbl">Place of Birth</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:28%">
        <span class="lbl">Language Spoken at Home</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:30%">
        <span class="lbl">Class Applying For *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:70%">
        <span class="lbl">Previous School Last Attended &nbsp;<span style="font-size:7pt;">(leave blank if none)</span></span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ── SECTION B: FAMILY INFORMATION ── --}}
  <div class="sec-div">B. FAMILY INFORMATION</div>

  <table class="fr">
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

  <table class="fr">
    <tr>
      <td style="width:33%">
        <span class="lbl">Father's Mobile / Phone *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:34%">
        <span class="lbl">Father's Aadhaar No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:33%">
        <span class="lbl">Father's Email Address</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
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

  <table class="fr">
    <tr>
      <td style="width:33%">
        <span class="lbl">Mother's Mobile / Phone</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:34%">
        <span class="lbl">Mother's Aadhaar No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:33%">
        <span class="lbl">Mother's Email Address</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:50%">
        <span class="lbl">Guardian Name &nbsp;<span style="font-size:7pt;">(if different from parents)</span></span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:50%">
        <span class="lbl">Guardian Occupation &amp; Contact</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr-last">
    <tr>
      <td>
        <span class="lbl">Sibling Name, Class &amp; Age &nbsp;<span style="font-size:7pt;">(if currently studying at Deep Griha Academy — mention class)</span></span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

</div>
</div>{{-- end page 1 --}}


{{-- ╔══════════════════════════════════════════════════════════════╗ --}}
{{-- ║                        PAGE  2                              ║ --}}
{{-- ╚══════════════════════════════════════════════════════════════╝ --}}
<div class="form-outer">

  {{-- ── MINI HEADER ── --}}
  <div class="mini-header">
    <table class="mini-tbl">
      <tr>
        <td>
          <div class="mini-school">Deep Griha Academy</div>
          <div class="mini-sub">Dattgaon Chola, Chinchol, Taluka Daund, District Pune</div>
        </td>
        <td class="mini-right">
          <strong>ADMISSION FORM</strong> — Page 2 of 2<br>
          <span style="font-size:7.5pt; color:#444;">Academic Year: {{ $academicYear }}</span>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── SECTION C: ADDRESS & CONTACT ── --}}
  <div class="sec-div">C. ADDRESS &amp; CONTACT DETAILS</div>

  <table class="fr">
    <tr>
      <td>
        <span class="lbl">Full Home Address &nbsp;<span style="font-size:7pt;">(House No., Street, Locality)</span></span>
        <span class="val-xl">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:30%">
        <span class="lbl">Village / Area</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:40%">
        <span class="lbl">City / Town *</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:30%">
        <span class="lbl">PIN Code</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
  </table>

  <table class="fr">
    <tr>
      <td style="width:30%">
        <span class="lbl">Contact No. (Residence / Office)</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:35%">
        <span class="lbl">In Case of Emergency — Cell</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:20%">
        <span class="lbl">Distance from School</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:15%">
        <span class="lbl">School Bus?</span>
        <span class="val"><span class="cb"></span>Yes <span class="cb"></span>No</span>
      </td>
    </tr>
  </table>

  {{-- ── SECTION D: MEDICAL ── --}}
  <div class="sec-div">D. MEDICAL INFORMATION</div>

  <table class="fr">
    <tr>
      <td style="width:60%">
        <span class="lbl">Any Known Allergies / Medical Conditions &nbsp;<span style="font-size:7pt;">(write "None" if not applicable)</span></span>
        <span class="val-xl">&nbsp;</span>
      </td>
      <td style="width:40%">
        <span class="lbl">Family Doctor's Name &amp; Phone No.</span>
        <span class="val-tall">&nbsp;</span>
      </td>
    </tr>
  </table>

  {{-- ── SECTION E: DOCUMENTS ── --}}
  <div class="sec-div">E. DOCUMENTS TO BE SUBMITTED &nbsp;<span style="font-size:8pt; font-weight:normal;">(tick whichever is enclosed)</span></div>

  <table class="fr">
    <tr>
      <td class="docs-td" style="width:50%">
        <span class="cb"></span>&nbsp; Birth Certificate<br>
        <span class="cb"></span>&nbsp; Aadhaar Card — Child<br>
        <span class="cb"></span>&nbsp; Aadhaar Card — Father<br>
        <span class="cb"></span>&nbsp; Aadhaar Card — Mother<br>
        <span class="cb"></span>&nbsp; Passport Size Photographs &nbsp;<span style="font-size:8pt;">(4 copies)</span>
      </td>
      <td class="docs-td" style="width:50%">
        <span class="cb"></span>&nbsp; Transfer / Leaving Certificate from previous school<br>
        <span class="cb"></span>&nbsp; Caste Certificate &nbsp;<span style="font-size:8pt;">(if applicable)</span><br>
        <span class="cb"></span>&nbsp; RTE Documents &nbsp;<span style="font-size:8pt;">(if applying under RTE)</span><br>
        <span class="cb"></span>&nbsp; Previous Year's Report Card<br>
        <span class="cb"></span>&nbsp; Other: ___________________________
      </td>
    </tr>
  </table>

  {{-- ── DECLARATION + SIGNATURE ── --}}
  <div class="sec-div">F. DECLARATION</div>

  <table class="fr">
    <tr>
      <td class="decl-td" style="width:65%">
        I / We, the parent(s) / guardian(s) of the above-named student, hereby declare that the information provided in this form is true, correct and complete to the best of my / our knowledge. I / We agree to abide by the rules, regulations and discipline of Deep Griha Academy. I / We understand that providing false or incomplete information may result in the cancellation of admission at any stage.
        <br><br>
        <strong>Place: </strong><span style="border-bottom:1px solid #000; display:inline-block; width:120px;">&nbsp;</span>
        &nbsp;&nbsp;&nbsp;
        <strong>Date: </strong><span style="border-bottom:1px solid #000; display:inline-block; width:100px;">&nbsp;</span>
      </td>
      <td style="width:35%; text-align:center; vertical-align:bottom; padding:10px 12px 8px 12px;">
        <div style="border:1px solid #000; height:65px; width:100%; margin-bottom:4px;">&nbsp;</div>
        <span style="font-size:8pt; color:#333;">Signature of Parent / Guardian</span>
        <br><span style="font-size:7.5pt; color:#555;">(Name: ________________________)</span>
      </td>
    </tr>
  </table>

  {{-- ── FOR OFFICE USE ONLY ── --}}
  <div class="office-hdr">FOR OFFICE USE ONLY</div>

  <table class="office-fr">
    <tr>
      <td style="width:38%">
        <span class="lbl">Admission Granted to Class</span>
        <span style="font-size:8.5pt;">
          <span class="cb"></span> Nursery &nbsp;
          <span class="cb"></span> L.KG &nbsp;
          <span class="cb"></span> U.KG &nbsp;
          <span class="cb"></span> Std. ______
        </span>
      </td>
      <td style="width:20%">
        <span class="lbl">Fee Category</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:22%">
        <span class="lbl">Receipt No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td style="width:20%">
        <span class="lbl">Date</span>
        <span class="val">&nbsp;</span>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="border-right:1px solid #000;">
        <span class="lbl">General Register ID / DGA Admission No.</span>
        <span class="val">&nbsp;</span>
      </td>
      <td colspan="2" style="text-align:center; vertical-align:bottom; padding:8px 12px 6px 12px;">
        <div style="border-bottom:1px solid #000; min-height:36px;">&nbsp;</div>
        <span style="font-size:8pt;">Manager's Signature</span>
      </td>
    </tr>
  </table>

</div>{{-- end page 2 --}}

</body>
</html>
