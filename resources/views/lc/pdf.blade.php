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
            width: 90%;
            margin: 0 auto;
            padding: 24px 20px 20px 20px;
        }

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

        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            font-size: 11.5px;
        }

        .meta-bar td {
            vertical-align: bottom;
        }

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

        .two-col-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
        }

        .two-col-table td {
            vertical-align: bottom;
            font-size: 11.5px;
        }

        /* ── Signature section ── */
        .sig-area {
            margin-top: 28px;
            width: 100%;
            border-collapse: collapse;
        }

        .sig-area td {
            vertical-align: bottom;
            font-size: 11px;
            padding-top: 0;
        }

        .sig-left {
            width: 25%;
        }

        .sig-middle-left {
            width: 25%;
            text-align: center;
        }

        .sig-middle-right {
            width: 25%;
            text-align: center;
        }

        .sig-right {
            width: 25%;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #000;
            padding-top: 3px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            margin-top: 40px;
        }

        .date-field {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
        }

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

        hr.divider {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
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
                    <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c0a4a5a5b0a7b2a9a8a1a1a3a1a4a5adb980a7ada1a9aceea3afad">[email&#160;protected]</a>
                </div>
                <div class="cert-title">LEAVING CERTIFICATE</div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- LC No / Register No -- full width, no clipping --}}
    <table class="meta-bar">
        <tr>
            <td style="width:40%;">
                No. <strong style="font-size:14px; letter-spacing:1px;">{{ $lc->lc_number }}</strong>
            </td>
            <td style="width:60%; text-align:center;">
                Register No. of Pupil :&nbsp;&nbsp;<strong style="font-size:13px;">
                    @if($lc->admission)
                        @if($lc->admission->class_id >= 4)
                            {{ $lc->admission->general_id ? $lc->admission->general_id : '—' }}
                        @else
                            {{ $lc->admission->dga_admission_no ? $lc->admission->dga_admission_no : '—' }}
                        @endif
                    @else
                        —
                    @endif
                </strong>
            </td>
        </tr>
    </table>

    {{-- FIELDS --}}

    <div class="field-row">
        Name Of Pupil :-
        <span class="field-line" style="min-width:380px;">{{ strtoupper($lc->pupil_name ?? '') }}</span>
    </div>

    <div style="border-bottom: 1px solid #000; height: 16px; margin-bottom: 9px;">&nbsp;</div>

    <div class="field-row">
        Name Of Mother :-
        <span class="field-line" style="min-width:360px;">{{ $lc->mother_name ?? '' }}</span>
    </div>

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

    <div class="field-row">
        Place Of Birth :-
        <span class="field-line" style="min-width:380px;">{{ $lc->place_of_birth ?? '' }}</span>
    </div>

    <div class="field-row">
        Date Of Birth :-
        <span class="field-line" style="min-width:380px;">
            {{ $lc->date_of_birth ? $lc->date_of_birth->format('d F Y') . ' (' . $lc->date_of_birth->format('d/m/Y') . ')' : '' }}
        </span>
    </div>

    <div style="border-bottom: 1px solid #000; height: 16px; margin-bottom: 9px;">&nbsp;</div>

    <div class="field-row">
        Last School Attended :-
        <span class="field-line" style="min-width:330px;">{{ $lc->last_school_attended ?? '' }}</span>
    </div>

    <div class="field-row">
        Date Of Admission :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->date_of_admission ? $lc->date_of_admission->format('d F Y') : '' }}
        </span>
    </div>

    <div class="field-row">
        Progress :-
        <span class="field-line" style="min-width:380px;">{{ $lc->progress ?? '' }}</span>
    </div>

    <div class="field-row">
        Conduct :-
        <span class="field-line" style="min-width:380px;">{{ $lc->conduct ?? '' }}</span>
    </div>

    <div class="field-row">
        Date Of Leaving :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->date_of_leaving ? $lc->date_of_leaving->format('d F Y') : '' }}
        </span>
    </div>

    <div class="field-row">
        Standard in Which Studying :-
        <span class="field-line" style="min-width:320px;">{{ $lc->standard_studying ?? '' }}</span>
    </div>

    <div class="field-row">
        And Since When :-
        <span class="field-line" style="min-width:360px;">
            {{ $lc->studying_since ? $lc->studying_since->format('d F Y') : '' }}
        </span>
    </div>

    <div class="field-row">
        Reason For Leaving School :-
        <span class="field-line" style="min-width:320px;">{{ $lc->reason_for_leaving ?? '' }}</span>
    </div>

    <div class="field-row">
        Remark :-
        <span class="field-line" style="min-width:380px;">{{ $lc->remarks ?? '' }}</span>
    </div>

    {{-- SIGNATURES — 4 columns so Sr.Clerk and Principal are separate --}}
    <table class="sig-area">
        <tr>
            <td class="sig-left" style="vertical-align:bottom;">
                {{ $lc->issue_place ?? 'Pune' }} :-
                <span class="date-field">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <br><br>
                Date :-
                <span class="date-field">&nbsp;{{ $lc->issue_date ? $lc->issue_date->format('d/m/Y') : '' }}&nbsp;</span>
            </td>
            <td class="sig-middle-left">
                <div class="sig-line">Class Teacher</div>
            </td>
            <td class="sig-middle-right">
                <div class="sig-line">Sr. Clerk</div>
            </td>
            <td class="sig-right">
                <div class="sig-line">Principal</div>
            </td>
        </tr>
    </table>

    {{-- NB --}}
    <div class="nb-box">
        <table class="nb-table">
            <tr>
                <td class="nb-label">N.B</td>
                <td class="nb-text">
                    Certified that the above the information is in accordance with the School Register.<br>
                    No change in any entry in this certificate shall be made except by the authority issuing it<br>
                    and any infringement of this rule is liable to be dealt with by ru