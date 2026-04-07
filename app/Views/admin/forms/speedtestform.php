<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Speedtest Report</title>

<style>
  @page {
    size: A4 portrait; /* Set landscape */
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
    width: 210mm; /* width of A4 portrait */
    min-height: 297mm;
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
    margin-left: 40px;
  }

  .logo-top {
    margin-left: 30px;
  }

</style>
</head>

<body>
<div class="a4page">


<table>
        <thead>
          <tr>
            <td colspan="9" style="border: none;">              
              <div style="text-align:right;">
                  <b>IM-017-0</b><br>
                  <small>24March2025</small>
                </div>
            </td>
          </tr>
        <tr>
          <td colspan="9" style="border: none;">
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
              <div class="title top-title">DAILY BANDWIDTH SPEED CHECK</div>
            </td>
            <td style="border: none; text-align: right;">

              <img src="<?= base_url('assets/img/DOH_logo.png') ?>" alt="DOH Logo" height="80"><br>

                
            </td>
          </tr>
        </table>
          </td>
          <td style="vertical-align: top; border: none;"></td>
        </tr>
    <tr>
      <th>Time and Date</th>
      <th>Node</th>
      <th>Ping (ms)</th>
      <th>Download (Mbps)</th>
      <th>Upload (Mbps)</th>
      <th>Checked By</th>
    </tr>
  </thead>

  <tbody>
  <?php if (!empty($records)): ?>
    <?php foreach ($records as $row): ?>
      <tr>
        <td><?= date('F j, Y - h:i A', strtotime($row['datetime'])) ?></td>
        <td><?= esc($row['node']) ?></td>
        <td><?= esc($row['ping']) ?></td>
        <td><?= esc($row['download']) ?></td>
        <td><?= esc($row['upload']) ?></td>
        <td><?= esc($row['checked_by']) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="6">No data found</td>
    </tr>
  <?php endif; ?>
  </tbody>
</table>

</div>

<script>
window.onload = function () {
    window.print();
};

window.onafterprint = function () {
    setTimeout(() => window.close(), 300);
};
</script>

</body>
</html>