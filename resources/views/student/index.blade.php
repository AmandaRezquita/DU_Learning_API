<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>import data student</h4>
                </div>
                <div class="card-body">
                    <form action="{{ url('student/import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="file" name="import_file" class="form-control"/>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>