<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PMS Report</title>
<style>
  @page {
    size: A4 landscape; /* Set landscape */
    margin: 10mm;
  }

  body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    color: #000;
    background: #fff;
    margin: 0;
    padding: 0;
  }

  .a4page {
    width: 277mm; /* width of A4 landscape */
    min-height: 190mm;
    margin: auto;
    padding: 10mm;
    background: #fff;
    box-sizing: border-box;
    overflow: hidden;
  }

  .header {
    text-align: center;
    border-bottom: 2px solid #000;
    padding-bottom: 10px;
  }

  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
  }

  .header-table td {
    vertical-align: middle;
    text-align: center;
  }

  .header-table img {
    width: 80px;
  }

  .title {
    font-weight: bold;
    text-align: center;
    margin: 10px 0;
    font-size: 14px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }

  th, td {
    border: 1px solid #000;
    padding: 4px 6px;
    text-align: center;
    vertical-align: middle;
  }

  th {
    background-color: #f5f5f5;
    font-weight: bold;
  }

  @media print {
    button {
      display: none;
    }
    body {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .table-responsive {
      overflow: visible !important; /* allow table to expand */
    }
  }

  .top-title{
    margin-left: 87px;
  }
</style>
</head>
<body>
  <div class="a4page">
<!-- <table class="header-table" style="border-collapse: collapse; border: none; width: 100%;" >
  <tr>
    <td rowspan="4" style="border: none; text-align: left;"><img src="<?= base_url('assets/img/cvmc_logo.png') ?>" alt="CVMC Logo" class="logo-top"></td>
    <td style="border: none; text-align: center;"  rowspan="4">
      <div class="top-title">Republic of the Philippines</div>
      <div class="top-title"><strong>DEPARTMENT OF HEALTH</strong></div>
      <div class="top-title"><strong>CAGAYAN VALLEY MEDICAL CENTER</strong></div>
      <div class="top-title">Regional Tertiary, Teaching, Training, and Research Medical Center</div>
    </td>
    <td style="border: none; text-align: right;" rowspan="4"><img src="<?= base_url('assets/img/DOH_logo.png') ?>" alt="DOH Logo"></td>
    <td style="border: none; text-align: right;"><b>IM-006-0</b></td>
  </tr>
  <tr>
    <td style="border: none;"></td>
  </tr>
  <tr>
    <td style="border: none;"></td>
  </tr>
  <tr>
    <td style="border: none;"></td>
  </tr>
</table>

    <div class="title">IT EQUIPMENT AND DEVICE PREVENTIVE MAINTENANCE CHECKLIST</div>
   -->

    <div class="table-responsive">
    <table class="result-table">
      <thead>
          <tr>
            <td colspan="14" style="border: none;">              
              <div style="text-align:right;">
                  <b>IM-006-0</b>
                </div>
            </td>
          </tr>
        <tr>
          <td colspan="14" style="border: none;">
             <table style="width: 100%; border: none;">
          <tr>
            <td style="border: none; text-align: left;">
              <img src="<?= base_url('assets/img/cvmc_logo.png') ?>" alt="CVMC Logo" height="80">
            </td>
            <td style="border: none; text-align: center;">
              <div class="top-title">Republic of the Philippines</div>
              <div class="top-title"><strong>DEPARTMENT OF HEALTH</strong></div>
              <div class="top-title"><strong>CAGAYAN VALLEY MEDICAL CENTER</strong></div>
              <div class="top-title">Regional Tertiary, Teaching, Training, and Research Medical Center</div>
              <div class="title top-title">IT EQUIPMENT AND DEVICE PREVENTIVE MAINTENANCE CHECKLIST</div>
            </td>
            <td style="border: none; text-align: right;">
              <img src="<?= base_url('assets/img/DOH_logo.png') ?>" alt="DOH Logo" height="80">
              
            </td>
          </tr>
        </table>
          </td>
          <td style="vertical-align: top; border: none;"></td>
        </tr>

   	<tr>
	<td colspan="14" style="text-align: left;">
	Area/Location: <strong><?= esc($area) ?></strong>
	</td>
 	</tr>
	<tr>
	<td colspan="14" style="text-align: left;">
	Month & Year: <strong><?= date('F Y', strtotime($month . '-01')) ?></strong>
	</td>
	</tr>
 <thead>
  <tr>
    <th rowspan="2">Date</th>
    <th rowspan="2">Time</th>
    <th rowspan="2">Computer Label</th>
    <th colspan="8">Check Points</th>
    <th rowspan="2">Remarks</th>
    <th rowspan="2">Performed By:</th>
    <th rowspan="2">Noted By:</th>
  </tr>
  <tr>
    <th>Keyboard</th>
    <th>Mouse</th>
    <th>Display</th>
    <th>VGA Cable</th>
    <th>HDD Space</th>
    <th>UPS/AVR</th>
    <th>Connect</th>
    <th>Power Cables</th>
  </tr>
</thead>

<tbody>
<?php if (!empty($records)): ?>
    <?php foreach ($records as $row): ?>
        <tr>
            <td><?= date('m/d/Y', strtotime($row['datetime'])) ?></td>
            <td><?= date('h:i A', strtotime($row['datetime'])) ?></td>
            <td><?= esc($row['computerlabel']) ?></td>

            <td><?= $row['keyboard'] ? '✔' : '' ?></td>
            <td><?= $row['mouse'] ? '✔' : '' ?></td>
            <td><?= $row['display'] ? '✔' : '' ?></td>
            <td><?= $row['vga'] ? '✔' : '' ?></td>
            <td><?= $row['hdd'] ? '✔' : '' ?></td>
            <td><?= $row['ups'] ? '✔' : '' ?></td>
            <td><?= $row['connect'] ? '✔' : '' ?></td>
            <td><?= $row['powercables'] ? '✔' : '' ?></td>

            <td><?= esc($row['remarks']) ?></td>
            <td><?= esc($row['performedby']) ?></td>
            <td><?= esc($row['notedby']) ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="13">No data found</td>
    </tr>
<?php endif; ?>
</tbody>
    </table>
  </div>
</div>





<script>
window.onload = function () {
    window.print();
};

window.onafterprint = function () {
    setTimeout(() => window.close(), 300);
};

window.onfocus = function () {
    setTimeout(() => window.close(), 500);
};
</script>
</body>
</html>
