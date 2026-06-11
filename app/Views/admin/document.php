<?php
$this->extend('layout/main');
$this->section('body');
?>

<div class="container mt-4">
    <h3 class="mb-3">Document Tracking</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row g-3 align-items-end mb-3">

                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <input type="text" id="filterType" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Title</label>
                    <input type="text" id="filterTitle" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Received By</label>
                    <input type="text" id="filterReceivedBy" class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button id="btnFilter" class="btn btn-warning w-100">
                        Filter
                    </button>

                    <button id="btnClearFilter" class="btn btn-info w-100">
                        Clear
                    </button>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <button
                        class="btn btn-success w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#documentModal">
                        Add Document
                    </button>
                </div>
            </div>

        </div>
    </div>

    <br>

    <table id="documentTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Received By</th>
                <th>Sent By</th>
                <th>Shelf</th>
                <th>Status</th>
                <th width="150">Action</th>
            </tr>
        </thead>
    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="documentForm">

                <input type="hidden" id="id">

                <div class="modal-header">
                    <h5 class="modal-title">Document Information</h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control" id="type" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text"
                               class="form-control"
                               id="title"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Received By</label>
                        <select class="form-control"
                                id="receivedby"
                                required>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sent By</label>
                        <input type="text"
                               class="form-control"
                               id="sendby"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shelf</label>
                        <select class="form-control" id="shelf">
                            <option value="1">Shelf 1</option>
                            <option value="2">Shelf 2</option>
                            <option value="3">Shelf 3</option>
                            <option value="4">Shelf 4</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="status">
                            <option value="0">Pending</option>
                            <option value="1">Received</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            class="btn btn-success">
                        Save
                    </button>

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Close
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    // TYPE SEARCH
    $('#type').select2({
        dropdownParent: $('#documentModal'),
        width: '100%',
        tags: true,
        placeholder: 'Search or type document type',
        ajax: {
            url: "<?= base_url('search/doctype') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params){
                return {
                    q: params.term
                };
            },
            processResults: function(data){
                return {
                    results: $.map(data, function(item){
                        return {
                            id: item.doc_type,
                            text: item.doc_type
                        };
                    })
                };
            }
        }
    });

$(document).on('shown.bs.modal', '#documentModal', function () {

    setTimeout(function () {

        if ($('#receivedby').hasClass("select2-hidden-accessible")) {
            $('#receivedby').select2('destroy');
        }

        $('#receivedby').select2({
            dropdownParent: $('#documentModal'),
            width: '100%',
            placeholder: 'Search technician',
            minimumInputLength: 1,
            ajax: {
                url: "<?= base_url('search/technician') ?>",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.name,
                            text: item.name
                        }))
                    };
                }
            }
        });

    }, 300);

});

    // DATATABLE
    var table = $('#documentTable').DataTable({

        ajax:{
            url:"<?= base_url('document/getData') ?>",
            data:function(d){
                d.type = $('#filterType').val();
                d.title = $('#filterTitle').val();
                d.receivedby = $('#filterReceivedBy').val();
            }
        },

        columns:[

            {data:'type'},
            {data:'title'},
            {data:'receivedby'},
            {data:'sendby'},
            {data:'shelf'},

            {
                data:'status',
                render:function(data){

                    if(data == 1){
                        return '<span class="badge bg-success">Received</span>';
                    }

                    return '<span class="badge bg-warning">Pending</span>';
                }
            },

            {
                data:null,
                render:function(data){

                    return `
                        <button class="btn btn-primary btn-sm editBtn"
                                data-id="${data.id}">
                            Edit
                        </button>

                        <button class="btn btn-danger btn-sm deleteBtn"
                                data-id="${data.id}">
                            Delete
                        </button>
                    `;
                }
            }

        ]

    });

    // FILTER
    $('#btnFilter').click(function(){
        table.ajax.reload();
    });

    $('#btnClearFilter').click(function(){

        $('#filterType').val('');
        $('#filterTitle').val('');
        $('#filterReceivedBy').val('');

        table.ajax.reload();

    });

    // SAVE DOCUMENT
    $('#documentForm').submit(function(e){

        e.preventDefault();

        let url = $('#id').val() == ''
            ? "<?= base_url('document/add') ?>"
            : "<?= base_url('document/update') ?>";

        $.ajax({

            url:url,
            type:'POST',

            data:{
                id:$('#id').val(),
                type:$('#type').val(),
                title:$('#title').val(),
                receivedby:$('#receivedby').val(),
                sendby:$('#sendby').val(),
                shelf:$('#shelf').val(),
                status:$('#status').val()
            },

            dataType:'json',

            success:function(res){

                if(res.status == 'success'){

                    $('#documentModal').modal('hide');

                    $('#documentForm')[0].reset();

                    $('#id').val('');

                    $('#type').val(null).trigger('change');
                    $('#receivedby').val(null).trigger('change');

                    table.ajax.reload();

                    Swal.fire({
                        icon:'success',
                        title:'Success',
                        text:res.message
                    });

                }

            }

        });

    });

    // EDIT
    $(document).on('click','.editBtn',function(){

        let id = $(this).data('id');

        $.ajax({

            url:"<?= base_url('document/edit') ?>/" + id,
            type:'GET',
            dataType:'json',

            success:function(res){

                $('#id').val(res.id);

                var typeOption = new Option(
                    res.type,
                    res.type,
                    true,
                    true
                );

                $('#type')
                    .append(typeOption)
                    .trigger('change');

                var recvOption = new Option(
                    res.receivedby,
                    res.receivedby,
                    true,
                    true
                );

                $('#receivedby')
                    .append(recvOption)
                    .trigger('change');

                $('#title').val(res.title);
                $('#sendby').val(res.sendby);
                $('#shelf').val(res.shelf);
                $('#status').val(res.status);

                $('#documentModal').modal('show');

            }

        });

    });

    // DELETE
    $(document).on('click','.deleteBtn',function(){

        let id = $(this).data('id');

        Swal.fire({
            title:'Delete this record?',
            icon:'warning',
            showCancelButton:true
        }).then((result)=>{

            if(result.isConfirmed){

                $.ajax({

                    url:"<?= base_url('document/delete') ?>",
                    type:'POST',

                    data:{
                        id:id
                    },

                    dataType:'json',

                    success:function(res){

                        Swal.fire(
                            'Deleted!',
                            res.message,
                            'success'
                        );

                        table.ajax.reload();

                    }

                });

            }

        });

    });

});
</script>

<?php $this->endSection(); ?>