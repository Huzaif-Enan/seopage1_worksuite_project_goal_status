<div class="modal fade" id="editcmsmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading">Edit CMS</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post"  autocomplete="off" id="updateCmsForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="id">
                <div class="modal-body">
                    <div class="row border-top-grey">
                        <div class="col-sm-12">
                            <div class="form-group my-3">
                                <label class="f-14 text-dark-grey mb-12" data-label="true" for="category_name">CMS Name</label>
                                <input type="text" class="form-control height-35 f-14" placeholder="Enter a cms name" name="cms_name2" id="cms_name2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_cms">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).on('click', '.update_cms_form', function() {
        let id = $(this).data('id');
        let cms_name = $(this).data('name');

        $('#id').val(id);
        $('#cms_name2').val(cms_name);
    });

    $(document).on('click', '#update_cms', function(e) {
        e.preventDefault();
        $('#update_cms').attr("disabled", true);
        $('#update_cms').html("Processing...");
        let id = $('#id').val();
        let cms_name = $('#cms_name2').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '/projects/update-cms/'+id,
            method: 'put',
            data: {
                id: id,
                cms_name: cms_name,
            },
            success: function(res) {
                if (res.status == 200) {
                    $('#update_cms').attr("disabled", false);
                    $('#update_cms').html("Submit");
                    window.location.reload();
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'CMS Update Successfully',
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            },
            error: function(err) {
                $('#update_cms').attr("disabled", false);
                $('#update_cms').html("Submit");
            }
        });
    });
</script>
