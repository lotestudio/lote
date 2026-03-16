<html lang="bg" style="font-size:80%; line-height:1.2;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body style="margin:0; font-family:Montserrat, Arial, sans-serif;font-size:80%; line-height:1.2;">
<div style="margin-left:auto; margin-right:auto; padding:1rem; max-width:600px;">
    <div>
        <div style="float: left; width: 48%;">
            <h2 style="font-size:0.75rem; line-height:1rem; text-transform:uppercase; color:#9ca3af;">Получател:</h2>
            <p><strong>{{$invoice->client_details['company']}}</strong><br>
                {{$invoice->client_details['address']}}<br>
                <strong>ЕИК/Булстат:</strong> {{$invoice->client_details['number']}}<br>
                <strong>ЗДДС №:</strong> {{$invoice->client_details['vat']}}<br>
                <strong>М.О.Л.</strong> {{$invoice->client_details['mol']}}<br>
            </p>
        </div>
        <div style="float: right; width: 48%; text-align: right">
            <h2 style="font-size:0.75rem; line-height:1rem; text-transform:uppercase; color:#9ca3af;">Доставчик:</h2>
            <p><strong>ЛОТЕ ЕООД</strong><br>
                СОФИЯ, бул. Янко Сакъзов 19<br>
                <strong>ЕИК/Булстат:</strong> 175260543<br>
                <strong>ЗДДС №:</strong> BG175260543<br>
                <strong>М.О.Л.</strong> Валентин Цанев<br>
            </p>
        </div>
        <div style="clear:both;"></div>
    </div>

    <div>
        <p style="text-align:center;">Дата: {{$invoice->date}} г.<br/>Място: гр. Русе</p>
        <hr style="margin-top:0.75rem; margin-bottom:0.75rem; border:0; border-top:1px solid #e5e7eb;">
        <div>
            <h1 style="float:left; font-size:1.875rem; line-height:2.25rem; font-weight:700;">
                Фактура #{{str_pad($invoice->num, 10, '0', STR_PAD_LEFT)}}
            </h1>
            <h1 style="float:right; text-align:right; font-size:1.875rem; line-height:2.25rem; font-weight:700; color:#d1d5db;">
                @if($type==='original')
                    ОРИГИНАЛ
                @else
                    КОПИЕ
                @endif
            </h1>
            <div style="clear:both;"></div>
        </div>
    </div>

    <table style="width:100%; margin-top:2rem; margin-bottom:2rem; border-collapse:collapse; border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb;">
        <thead>
        <tr style="border-bottom:1px solid #e5e7eb; font-size:0.75rem; line-height:1rem;">
            <th style="padding-top:1rem; padding-bottom:1rem; text-align:left;">№</th>
            <th style="padding-top:1rem; padding-bottom:1rem; text-align:left;">Наименование</th>
            <th style="padding-top:1rem; padding-bottom:1rem; text-align:right;">Количество</th>
            <th style="padding-top:1rem; padding-bottom:1rem; text-align:right;">Ед. цена</th>
            <th style="padding-top:1rem; padding-bottom:1rem; text-align:right;">Стойност</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($invoice->services as $service)
            <tr style="font-size:0.75rem; line-height:1rem;">
                <td>{{ $loop->index +1  }}. </td>
                <td style="width: 40%;">{{ $service->description }}</td>
                <td style="text-align:right; padding-top:0.5rem; padding-bottom:0.5rem;">{{ $service->items }}</td>
                <td style="text-align:right;">{{ $service->value->formatted()}}</td>
                <td style="text-align:right;">{{ $service->total->formatted()}}</td>
            </tr>
        @endforeach
        <tr style="border-top:1px solid #e5e7eb;">
            <td colspan="4" style="text-align:right; padding-top:0.5rem; padding-bottom:0.5rem; font-size:0.875rem; line-height:1.25rem;">
                Междинна сума:<br>
                Данъчна основа:<br>
                Начислен 20% ДДС:<br>
                Обща сума за плащане:<br>
            </td>
            <td style="text-align:right; font-size:0.875rem; line-height:1.25rem;">
                {{$invoice->total->formatted()}}<br>
                {{$invoice->total->formatted()}}<br>
                {{$invoice->vat->formatted()}}<br>
                <strong>{{$invoice->total_with_vat->formatted()}}</strong>
            </td>
        </tr>
        </tbody>
    </table>

    <div>
        <div style="float:left; width:48%;">
            <strong>Начин на плащане:</strong> по банков път<br>
            <strong>Дата на данъчното <br/>събитие/плащане:</strong> {{$invoice->created_at->format('d.m.Y')}} г.
            <br><br><br>
            <strong>Получател:</strong><br/>
            ({{$invoice->recipient ?? $invoice->client_details['mol']}})
        </div>

        <div style="float:right; width:48%; text-align:right; position:relative;">
            @if($signed==='1')
                <img width="180px" src="{{url('img/invoices/sign_stamp.png')}}" alt="stamp" style="position:absolute; top:45px; left:60px;"/>
            @endif
            <strong>Банка:</strong> ТБ Алианц България АД<br>
            <strong>BIC:</strong> BUINBGSF<br>
            <strong>Банкова сметка:</strong> BG52BUIN76041012107912
            <br><br><br>
            <strong>Съставил:</strong> @if(!$signed) 010572LOTE @endif
            <br/>
            (Валентин Цанев)
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
</body>
</html>
