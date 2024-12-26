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

                <hr>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody> 
                        @foreach ( $student as $item )
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->phone_number}}</td>
                            <td>{{$item->email}}</td>
                        </tr>                        
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
