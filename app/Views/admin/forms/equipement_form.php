<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>INVENTORY OF IT EQUIPMENT AND DEVICES</title>
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
    thead {
  display: table-header-group;
}
  }

</style>
</head>
<body>
  <div class="a4page">
    <div class="table-responsive">
    <table class="result-table">

<thead>

  <tr>
    <th colspan="9" style="border: none;">
      <div style="text-align:right;">
        <b>IM-004-0</b>
      </div>
    </th>
  </tr>


  <tr>
    <th colspan="9" style="border: none;">
      <table style="width: 100%; border: none;">
        <tr>
          <td style="border: none; text-align: left;">
            <img src="<?= base_url('assets/img/cvmc_logo.png') ?>" height="80">
          </td>
          <td style="border: none; text-align: center;">
            <div>Republic of the Philippines</div>
            <div><strong>DEPARTMENT OF HEALTH</strong></div>
            <div><strong>CAGAYAN VALLEY MEDICAL CENTER</strong></div>
            <div>Regional Tertiary, Teaching, Training, and Research Medical Center</div>
            <div><strong>INVENTORY OF IT EQUIPMENT AND DEVICES</strong></div>
            <div><b>As of:</b> <?= date('F, Y') ?></div>
          </td>
          <td style="border: none; text-align: right;">
            <img src="<?= base_url('assets/img/DOH_logo.png') ?>" height="80">
          </td>
        </tr>
      </table>
    </th>
  </tr>

  <!-- COLUMN HEADERS -->
  <tr>
    <th>No.</th>
    <th>Equipment Type</th>
    <th>Model</th>
    <th>Label(If any)</th>
    <th>Accountable Area/Personnel</th>
    <th>Description/Specification</th>
    <th>Acquisition Date</th>
    <th>Estimated Life Span</th>
    <th>Remarks</th>
  </tr>

</thead>

  <tbody>
    <?php $no = 1; ?>
    <?php foreach ($equipmentList as $eq): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $eq['type'] ?></td>
        <td><?= $eq['model'] ?></td>
        <td><?= $eq['label'] ?></td>
        <td><?= $eq['AccountableArea'] ?></td>
        <td><?= $eq['description'] ?></td>
        <td>
          <?= (empty($eq['acquisitiondate']) || $eq['acquisitiondate'] == '0000-00-00') 
              ? '-' 
              : (new DateTime($eq['acquisitiondate']))->format('F j, Y'); ?>
        </td>
        <td>
          <?= (empty($eq['estimatedlife']) || $eq['estimatedlife'] == '0000-00-00') 
              ? '-' 
              : (new DateTime($eq['estimatedlife']))->format('F j, Y'); ?>
        </td>
        <td><?= $eq['remarks'] ?></td>
      </tr>
    <?php endforeach; ?>
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
