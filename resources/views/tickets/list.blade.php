@extends("base")

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Twoje zgłoszenia</h1>
        </div>
    </div>
    <div class="panel panel-default table-responsive">
        <div class="panel-body btn-margins">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 10%;">ID ZGŁ.</th>
                        <th style="width: 50%;">TYTUŁ</th>
                        <th style="width: 20%;">STATUS</th>
                        <th style="width: 20%;">DODANO</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td><a href="{{ \Illuminate\Support\Facades\URL::route('ticket.show', ['id' => $ticket->Id]) }}">#{{ $ticket->Id }}</a></td>
                            <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ \App\Libraries\AppLibrary::AddEllipsis($ticket->Title) }}</td>
                            <td>
                                @if ($ticket->Status == 0)
                                    <span class="badge badge-primary">Oczekujące</span>
                                @elseif($ticket->Status == 1)
                                    <span class="badge badge-info">Przyjęte</span>
                                @elseif($ticket->Status == 2)
                                    <span class="badge badge-warning">W realizacji</span>
                                @else ($ticket->Status == 3)
                                    <span class="badge badge-success">Zakończone</span>
                                @endif
                            </td>
                            <td>
                                {{ \Jenssegers\Date\Date::createFromTimestamp($ticket->Timestamp)->diffForHumans() }}<br />
                                <small style="color: grey;">{{ \Jenssegers\Date\Date::createFromTimestamp($ticket->Timestamp) }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;"><em>Nie utworzyłeś jeszcze żadnego zgłoszenia.</em></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.panel-->
@endsection