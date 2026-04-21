<?php if (!$todayTroubles): ?>
<tr>
    <td colspan="10" class="text-center text-muted">
        No records today
    </td>
</tr>
<?php endif; ?>

<?php foreach ($todayTroubles as $row): ?>
<tr>

    <!-- AUTO NUMBER COLUMN -->
    <td></td>
    <td><?= date('h:i A', strtotime($row['time'])) ?></td>

    <td><?= esc($row['name']) ?></td>
    <td><?= esc($row['location']) ?></td>
    <td><?= esc($row['description']) ?></td>

    <td>
        <span class="badge <?= $row['status'] === 'Ongoing' ? 'bg-warning' : 'bg-success' ?>">
            <?= esc($row['status']) ?>
        </span>
    </td>
   <td>
    <?php if (empty($row['tech_name'])): ?>

        <!-- SHOW SELECT UI -->
        <div class="position-relative" data-trouble-id="<?= $row['id'] ?>">
            <input type="text"
                class="form-control tech-input"
                autocomplete="off"
                placeholder="Search IT technical">

            <div class="techList list-group position-absolute w-100 d-none"
                style="max-height:200px; overflow-y:auto; z-index:1056;">
            </div>
        </div>

    <?php else: ?>

        <!-- SHOW NAME -->
        <?= esc($row['tech_name']) ?>

    <?php endif; ?>
</td>
    <td>
        <?php if (empty($row['time_started']) && $row['status'] === 'Ongoing'): ?>
            
            <form action="<?= site_url('trouble/startNow') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">
                    Start Now
                </button>
            </form>

        <?php elseif (empty($row['time_started']) && $row['status'] === 'Done'): ?>

            <span>
                -
            </span>

        <?php else: ?>

            <span>
                <?= date('h:i A', strtotime($row['time_started'])) ?>
            </span>

        <?php endif; ?>
        </td>
    
    <td>
    <?php if ($row['status'] === 'Ongoing' && !empty($row['Acknoby'])): ?>

        <?php if (empty($row['time_started'])): ?>
            <span class="text-muted">Start first</span>

        <?php else: ?>

            <form action="<?= site_url('trouble/markDone') ?>"
                  method="post"
                  enctype="multipart/form-data"
                  class="d-flex gap-2">

                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="d-flex flex-column w-100">

                    <input type="text"
                           name="remarks"
                           class="form-control form-control-xl mb-2"
                           placeholder="Enter remarks">

                    <!-- 📸 Upload image -->
                   <input type="file"
                    name="proof_image"
                    id="proof_image_<?= $row['id'] ?>"
                    class="d-none"
                    accept="image/*">

        <button type="button"
                class="btn btn-primary btn-sm mb-2"
                onclick="document.getElementById('proof_image_<?= $row['id'] ?>').click();">
            📸 Upload Image
        </button>


                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-success btn-xl">
                            ✔
                        </button>

                        <button type="button"
                                class="btn btn-danger btn-xl endorse-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#endorseModal"
                                data-id="<?= $row['id'] ?>">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>
                </div>
            </form>

        <?php endif; ?>

<?php else: ?>
    <div>
        <?= esc($row['remarks'] ?? '-') ?>

        <?php if (!empty($row['image'])): ?>
            <br>

           <a href="<?= base_url('assets/img/uploads/' . $row['image']) ?>" 
               target="_blank"
               class="text-primary small">
                <u>See Attached Image </u>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</td>

    <td>
        <?php
        if (!empty($row['completion_time']) && !empty($row['time_started'])) {
            $start = new DateTime($row['time_started']);
            $end   = new DateTime($row['completion_time']);
            $diff = $start->diff($end);
            $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

            if ($minutes < 60) {
                echo $minutes . ' min';
            } else {
                $hours = floor($minutes / 60);
                $mins  = $minutes % 60;
                echo $hours . ' hr' . ($hours > 1 ? 's' : '');
                if ($mins > 0) echo ' ' . $mins . ' min';
            }
        } else {
            echo '-';
        }
        ?>
    </td>

<td>
    <?php if (!empty($row['Acknoby'])): ?>

        <div class="mb-1">
            <strong>ID Number:</strong> <?= esc($row['ack_id_num']) ?>
        </div>

        <div>
            <strong>Full Name:</strong> <?= esc($row['ack_full_name']) ?>
        </div>

        <div class="mb-1">
            <strong>Remarks:</strong><br>
            <?= !empty($row['ack_remarks']) ? esc($row['ack_remarks']) : 'No remarks' ?>
        </div>

    <?php else: ?>
        <form action="<?= site_url('trouble/saveAck') ?>" method="post">
            <?= csrf_field() ?>

            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <input type="text" 
                   name="id_num"
                   class="form-control form-control-sm mb-1" 
                   placeholder="ID Number" 
                   required>

            <input type="text" 
                   name="full_name"
                   class="form-control form-control-sm mb-1" 
                   placeholder="Full Name" 
                   required>

            <input type="text" 
                   name="remarks"
                   class="form-control form-control-sm mb-1" 
                   placeholder="Remarks" 
                   >

            <button type="submit" class="btn btn-primary btn-sm w-100">
                Save
            </button>
        </form>
    <?php endif; ?>
</td>

</tr>

<?php endforeach; ?>
