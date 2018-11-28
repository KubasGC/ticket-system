@extends('base')

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Nowe zgłoszenie</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form id="messageform" method="post" action="/ticket/addNew" class="form-horizontal row-border">
                        @csrf
                        <div class="form-group">
                            <label class="col-md-2 control-label">Tytuł zgłoszenia</label>
                            <div class="col-md-10">
                                <input type="text" name="title" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Treść zgłoszenia</label>
                            <div class="col-md-10">
                                <textarea id="editor1" name="message" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="form-group text-right" style="padding-right: 10px;">
                            <input type="submit" id="messagebutton" class="btn btn-primary" value="Dodaj nowe zgłoszenie">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div><!--/.row-->
@endsection

@section("scripts")
    <script src="https://cdn.ckeditor.com/4.10.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'editor1', { removeButtons: "Source" } );
    </script>
    <script>
        $(function() {
            $("#messageform").submit(function() {
                $("#messagebutton").attr("disabled", "disabled");
                return true;
            });
        });
    </script>
@endsection