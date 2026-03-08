<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Leaving Certificate — {{ $lc->lc_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 24px 36px 20px 36px;
        }

        /* ── Header ─────────────────────────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .logo-cell {
            width: 70px;
            vertical-align: top;
            padding-right: 10px;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            border: 1px solid #aaa;
            display: block;
            text-align: center;
            line-height: 60px;
            font-size: 9px;
            color: #999;
        }

        .school-cell {
            vertical-align: top;
            text-align: center;
        }

        .society-name {
            font-size: 13px;
            font-weight: bold;
        }

        .reg-info {
            font-size: 10px;
            color: #222;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            margin: 3px 0 2px 0;
        }

        .school-address {
            font-size: 10.5px;
            color: #222;
        }

        .cert-title {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* ── LC No / Register No row ─────────────────────────────────────── */
        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            font-size: 11.5px;
        }

        .meta-bar td {
            vertical-align: bottom;
        }

        /* ── Field rows ──────────────────────────────────────────────────── */
        .field-row {
            margin-bottom: 9px;
            font-size: 11.5px;
            line-height: 1.4;
        }

        .field-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 340px;
            height: 16px;
            vertical-align: bottom;
            padding: 0 3px 1px 3px;
            font-weight: bold;
        }

        .field-line-short {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 200px;
            height: 16px;
            vertical-align: bottom;
            padding: 0 3px 1px 3px;
            font-weight: bold;
        }

        .field-line-fill {
            display: block;
            border-bottom: 1px solid #000;
            width: 100%;
            height: 16px;
            vertical-align: bottom;
            padding: 0 3px 1px 3px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Two column inline fields */
        .two-col-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
        }

        .two-col-table td {
            vertical-align: bottom;
            font-size: 11.5px;
        }

        /* ── Signature section ───────────────────────────────────────────── */
        .sig-area {
            margin-top: 28px;
            width: 100%;
            border-collapse: collapse;
        }

        .sig-area td {
            vertical-align: bottom;
            font-size: 11px;
        }

        .sig-left {
            width: 22%;
        }

        .sig-middle {
            width: 38%;
            text-align: center;
        }

        .sig-right {
            width: 40%;
            text-align: right;
        }

        .sig-line {
            border-top: 1px solid #000;
            padding-top: 2px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }

        .date-field {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
        }

        /* ── NB footer ───────────────────────────────────────────────────── */
        .nb-box {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 10px;
            line-height: 1.5;
        }

        .nb-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nb-label {
            font-weight: bold;
            font-size: 11px;
            vertical-align: top;
            width: 35px;
            padding-right: 4px;
        }

        .nb-text {
            font-size: 10px;
            vertical-align: top;
        }

        /* Fee note */
        .fee-note {
            margin-top: 8px;
            font-size: 10px;
            color: #8B0000;
            font-style: italic;
        }

        /* Horizontal divider */
        hr.divider {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                {{-- If you have a logo file accessible to DomPDF, replace with:
                     <img src="{{ public_path('images/dga-logo.png') }}" width="60"> --}}
                <div class="logo-placeholder">LOGO</div>
            </td>
            <td class="school-cell">
                <div class="society-name">Deep Griha Society's</div>
                <div class="reg-info">
                    (Reg. No.S.R.A. MAH/1128/PN ) (B.P.T.F.988)<br>
                    (Income - Tax Exemption U/S 80 G)
                </div>
                <div class="school-name">Deep Griha Academy</div>
                <div class="school-address">
                    Vidyanagari, Deulgaon Gada, Chaufula, Taluka.Daund District.Pune<br>
                    deepgrihaacademy@gmail.com
                </div>
                <div class="cert-title">LEAVING CERTIFICATE</div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── LC No / Register No ─────────────────────────────────────────────── --}}
    <table class="meta-bar">
        <tr>
            <td style="width:50%;">
                No. <strong style="font-size:14px; letter-spacing:1px;">{{ $lc->lc_number }}</strong>
            </td>
            <td style="width:50%; text-align:right;">
                Register No. of Pupil :
                <span class="date-field" style="min-width:130px;">&nbsp;{{ $lc->student->gr_number ?? '' }}&nbsp;</span>
            </td>
        </tr>
    </table>

    {{-- ── FIELDS ───────────────────────────────────────────────────────────── --}}

    {{-- Name Of Pupil --}}
    <div class="field-row">
        Name Of Pupil :-
        <span class="field-line" style="min-width:380px;">
            {{ strtoupper($lc->pupil_name ?? '') }}
        </span>
    </div>

    {{-- blank second line for long names --}}
    <div style="border-bottom: 1px solid #000; height: 16px; margin-bottom: 9px;">&nbsp;</div>

    {{-- Name Of Mother --}}
    <div class="field-row">
        Name Of Mother :-
        <span class="field-line" style="min-width:360px;">{{ $lc->mother_name ?? '' }}</span>
    </div>

    {{-- Race/Caste + Nationality --}}
    <table class="two-col-table">
        <tr>
            <td style="width:55%;">
                Race And Caste :-
                <span class="field-line-short" style="min-width:180px;">{{ $lc->race_and_caste ?? '' }}</span>
            </td>
            <td style="width:45%;">
                Nationality :-
                <span class="field-line-short" style="min-width:130px;">{{ $lc->nationality ?? 'Indian' }}</span>
            </td>
        </tr>
    </table>

    {{-- Place Of Birth --}}
    <div class="field-row">
        Place Of Birth :-
        <span class="field-line" style="min-width:380px;">{{ $lc->place_of_birth ?? '' }}</span>
    </div>

    {{-- Date Of Birth --}}
    <div class="field-row">
        Date Of Birth :-
        <span class="field-line" style="min-width:380px;">
            {{ $lc->date_of_birth ? $lc->date_of_birth->format('d F Y') . ' (' . $lc->date_of_birth->format('d/m/Y') . ')' : '' }}
        </span>
    </div>

    {{-- blank second line --}}
    <div style="border-bottom: 1px solid #000; height: 16px; margin-bottom: 9px;">&nbsp;</div>

    {{-- Last School Attended --}}
    <div class="field-row">
        Last School Attended :-
        <span class="field-line" style="min-width:330px;">{{ $lc->last_school_attended ?? '' }}</span>
    </div>

    {{-- Date Of Admission --}}
    <div class="field-row">
        Date Of Admission :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->date_of_admission ? $lc->date_of_admission->format('d F Y') : '' }}
        </span>
    </div>

    {{-- Progress --}}
    <div class="field-row">
        Progress :-
        <span class="field-line" style="min-width:380px;">{{ $lc->progress ?? '' }}</span>
    </div>

    {{-- Conduct --}}
    <div class="field-row">
        Conduct :-
        <span class="field-line" style="min-width:380px;">{{ $lc->conduct ?? '' }}</span>
    </div>

    {{-- Date Of Leaving --}}
    <div class="field-row">
        Date Of Leaving :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->date_of_leaving ? $lc->date_of_leaving->format('d F Y') : '' }}
        </span>
    </div>

    {{-- Standard In Which Studying --}}
    <div class="field-row">
        Standard in Which Studying :-
        <span class="field-line" style="min-width:320px;">{{ $lc->standard_studying ?? '' }}</span>
    </div>

    {{-- And Since When --}}
    <div class="field-row">
        And Since When :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->studying_since ? $lc->studying_since->format('d F Y') : '' }}
        </span>
    </div>

    {{-- Reason For Leaving --}}
    <div class="field-row">
        Reason For Leaving School :-
        <span class="field-line" style="min-width:320px;">{{ $lc->reason_for_leaving ?? '' }}</span>
    </div>

    {{-- Remark --}}
    <div class="field-row">
        Remark :-
        <span class="field-line" style="min-width:380px;">{{ $lc->remarks ?? '' }}</span>
    </div>

    @if($lc->fees_due > 0)
        <div class="fee-note">
            * Outstanding fees of &#x20B9;{{ number_format($lc->fees_due, 2) }} were due at the time of issuing this certificate.
        </div>
    @endif

    {{-- ── SIGNATURES ───────────────────────────────────────────────────────── --}}
    <table class="sig-area" style="margin-top: 30px;">
        <tr>
            <td class="sig-left" style="vertical-align: bottom;">
                {{ $lc->issue_place ?? 'Pune' }} :-
                <span class="date-field">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <br><br>
                Date :-
                <span class="date-field">
                    &nbsp;{{ $lc->issue_date ? $lc->issue_date->format('d/m/Y') : '' }}&nbsp;
                </span>
            </td>
            <td class="sig-middle">
                <br><br><br>
                <div class="sig-line">Class Teacher</div>
                <br>
                <div class="sig-line">Sr.Clerk</div>
            </td>
            <td class="sig-right" style="vertical-align: bottom;">
                <br><br><br><br><br>
                <div class="sig-line" style="text-align: right; padding-right: 20px;">Principal</div>
            </td>
        </tr>
    </table>

    {{-- ── NB ──────────────────────────────────────────────────────────────── --}}
    <div class="nb-box">
        <table class="nb-table">
            <tr>
                <td class="nb-label">N.B</td>
                <td class="nb-text">
                    Certified that the above the information is in accordance with the School Register.<br>
                    No change in any entry in this certificate shall be made except by the authority issuing it<br>
                    and any infringement of this rule is liable to be dealt with by rustication or by suitable punishment.
                </td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
