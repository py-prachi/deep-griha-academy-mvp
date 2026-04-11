<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #fff; }

.page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 10mm 12mm; }
.page-break { page-break-after: always; }

/* HEADER */
.rc-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
.rc-header .school-name { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
.rc-header .school-address { font-size: 10px; color: #333; margin-top: 2px; }
.rc-header .rc-title { font-size: 14px; font-weight: bold; margin-top: 6px; letter-spacing: 2px; text-transform: uppercase; }

/* STUDENT INFO */
.student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; border: 1px solid #999; padding: 8px 10px; margin-bottom: 10px; }
.student-info .info-row { display: flex; gap: 4px; }
.student-info .label { font-weight: bold; min-width: 100px; }

/* ATTENDANCE */
.attendance-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.attendance-table th, .attendance-table td { border: 1px solid #999; padding: 4px 8px; text-align: center; }
.attendance-table th { background: #f0f0f0; font-weight: bold; }

/* MARKS TABLE */
.marks-section h3 { font-size: 12px; font-weight: bold; background: #333; color: #fff; padding: 4px 8px; margin-bottom: 0; }
.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.marks-table th, .marks-table td { border: 1px solid #999; padding: 3px 6px; font-size: 10.5px; }
.marks-table th { background: #e8e8e8; font-weight: bold; text-align: center; }
.marks-table td.subject-name { font-weight: normal; }
.marks-table td.grade-cell { text-align: center; font-weight: bold; }
.marks-table .sub-header th { font-size: 9px; background: #f5f5f5; text-align: center; }

/* GRADE KEY */
.grade-key { border: 1px solid #ccc; padding: 6px 10px; margin-bottom: 10px; font-size: 10px; }
.grade-key .key-title { font-weight: bold; margin-bottom: 3px; }
.grade-key .key-items { display: flex; flex-wrap: wrap; gap: 6px 16px; }

/* REMARKS */
.remarks-section { display: grid; gap: 10px; margin-bottom: 14px; }
.remarks-section.two-col { grid-template-columns: 1fr 1fr; }
.remarks-section.one-col { grid-template-columns: 1fr; }
.remarks-box { border: 1px solid #999; padding: 8px; min-height: 80px; }
.remarks-box .rm-title { font-weight: bold; font-size: 11px; border-bottom: 1px solid #ccc; margin-bottom: 6px; padding-bottom: 3px; }
.remarks-text { font-size: 11px; line-height: 1.6; white-space: pre-wrap; }

/* SIGNATURES */
.signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 20px; margin-top: 20px; text-align: center; }
.sig-box { border-top: 1px solid #333; padding-top: 4px; font-size: 10px; }

/* TOOLBAR (screen only) */
.print-toolbar { background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 8px 20px; display: flex; align-items: center; }

@media print {
    body { background: #fff; }
    .page { margin: 0; padding: 8mm 10mm; }
    .no-print { display: none !important; }
    .print-toolbar { display: none !important; }
}
</style>
