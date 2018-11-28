@extends("base")

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Szczegóły zgłoszenia</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="form-horizontal row-border">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Identyfikator zgłoszenia</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" value="#{{ $ticketData->Id }}" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Tytuł</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" value="{{ $ticketData->Title }}" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Data utworzenia</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" value="{{ \Jenssegers\Date\Date::createFromTimestamp($ticketData->Timestamp) }}" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Przyjęte przez</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" value="{{ $adminName }}" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Status</label>
                            <div class="col-md-10">
                                @if ($ticketData->Status == 0)
                                    <span class="badge badge-primary">Oczekujące</span>
                                @elseif($ticketData->Status == 1)
                                    <span class="badge badge-info">Przyjęte</span>
                                @elseif($ticketData->Status == 2)
                                    <span class="badge badge-warning">W realizacji</span>
                                @else ($ticketData->Status == 3)
                                    <span class="badge badge-success">Zakończone</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--/.row-->

    @if(\App\Libraries\AppLibrary::CheckAdminPerm(\Kubas\Oauth\OauthProvider::Instance()->getUserInfo()->toArray()['group']))
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Narzędzia administratora</div>
                <div class="panel-body">
                    <form method="post" action="/ticket/saveAdmin/{{ $ticketData->Id }}" class="form-horizontal row-border">
                        @csrf
                        <div class="form-group">
                            <label class="col-md-2 control-label">Zmień status</label>
                            <div class="col-md-10">
                                <select name="changeStatus" class="form-control">
                                    <option value="-1">--Nie zmieniaj statusu--</option>
                                    <option value="0">Oczekujące</option>
                                    <option value="1">Przyjęte</option>
                                    <option value="2">W realizacji</option>
                                    <option value="3">Zakończone</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">Przypisz zgłoszenie</label>
                            <div class="col-md-10">
                                {{--<input class="form-control" type="number" name="changeAdmin" placeholder="Podaj identyfikator administratora">--}}
                                <select class="form-control" name="changeAdmin">
                                    <option value="null">--Nie zmieniaj przypisania--</option>
                                    @foreach (\App\Libraries\AppLibrary::GetAdmins() as $admin)
                                        <option value="{{ $admin->member_id }}">{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <input type="submit" class="btn btn-md btn-primary" value="Zapisz zmiany">
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default chat">
                <div class="panel-heading">Treść</div>
                <div class="panel-body">
                    <ul>
                        @foreach($ticketMessages as $message)
                            @if($message->User == 0)
                                <li class="right clearfix">
                                    <div class="chat-body clearfix">
                                        <div class="header">
                                            <strong class="primary-font">System</strong> <small class="text-muted">{{ \Jenssegers\Date\Date::createFromTimestamp($message->Timestamp)->diffForHumans() }}</small>
                                        </div>
                                        <p>{{ $message->Content }}</p>
                                    </div>
                                </li>
                            @else
                                <li class="{{ $message->User == $ticketData->Owner ? 'left' : 'right' }} clearfix">
                                    <span class="chat-img pull-{{ $message->User == $ticketData->Owner ? 'left' : 'right' }}"><img src="{{ $message->forumInfo->photoUrl }}" width="60" height="60" class="img-circle"></span>
                                    <div class="chat-body clearfix">
                                        <div class="header">
                                            <strong class="primary-font">{{ $message->forumInfo->name }}</strong> <small class="text-muted">{{ \Jenssegers\Date\Date::createFromTimestamp($message->Timestamp)->diffForHumans() }}</small>
                                        </div>
                                        <p>{!! $message->Content !!}</p>
                                    </div>
                                </li>
                            @endif

                        @endforeach
                    </ul>
                </div>
                @if($ticketData->Status != 3)
                    <div class="panel-footer">
                        <form id="messageform" method="post" action="/ticket/addMessage/{{ $ticketData->Id }}">
                            @csrf
                            <textarea id="editor1" name="message" class="form-control"></textarea><br />
                            <button type="submit" class="btn btn-primary btn-md" id="messagebutton">Odpowiedz</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
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