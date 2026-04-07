<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation — {{ $invoice->client_name }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.35;
            max-width: 210mm;
            margin: 0 auto;
            padding: 12mm 15mm 18mm;
            background: #fff;
        }

        .quotation-sheet {
            margin: 0;
        }

        .letterhead-img {
            width: 100%;
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 0 10px;
        }

        .quotation-subheader {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: start;
            gap: 10px 16px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            margin: 0 0 16px;
        }

        .subheader-contact {
            text-align: left;
            line-height: 1.35;
        }

        .subheader-contact .contact-line {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 3px;
        }

        .subheader-contact svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
        }

        .subheader-title {
            text-align: center;
            justify-self: center;
            align-self: start;
            padding-top: 2px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .subheader-date {
            text-align: right;
            justify-self: end;
            white-space: nowrap;
            padding-top: 2px;
        }

        .recipient-block {
            margin-bottom: 0.35em;
        }

        .recipient-block div:first-child {
            margin-bottom: 0.15em;
        }

        .salutation {
            font-weight: bold;
            font-style: italic;
            margin: 1em 0 0.35em 0;
        }

        .subject-line {
            font-weight: bold;
            font-style: italic;
            margin: 0 0 1.15em 0;
        }

        table.quotation {
            width: 100%;
            border-collapse: collapse;
            margin: 0.5em 0 1.15em 0;
            font-size: 11pt;
        }

        table.quotation th,
        table.quotation td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.quotation thead th {
            font-weight: bold;
        }

        table.quotation th.desc,
        table.quotation td.desc {
            text-align: left;
        }

        table.quotation th.size,
        table.quotation td.size {
            text-align: left;
        }

        table.quotation th.sn,
        table.quotation td.sn {
            text-align: center;
            width: 3.2em;
        }

        table.quotation th.num,
        table.quotation td.num {
            text-align: right;
            white-space: nowrap;
        }

        table.quotation tfoot td.total-label {
            font-weight: bold;
            text-align: left;
        }

        table.quotation tfoot td.total-label .paren {
            font-weight: normal;
        }

        table.quotation tfoot td.num {
            font-weight: bold;
        }

        .closing {
            margin: 0.9em 0;
            text-align: left;
        }

        .notes-heading {
            font-weight: bold;
            margin: 1em 0 0.35em 0;
        }

        .notes-list {
            margin: 0;
            padding-left: 2em;
            list-style-type: disc;
        }

        .notes-list li {
            margin: 0.15em 0;
        }

        .quotation-sheet--page-after {
            page-break-after: always;
            break-after: page;
        }

        @media print {
            body {
                padding: 10mm 12mm;
            }

            .quotation-sheet--page-after {
                page-break-after: always;
                break-after: page;
            }
        }
    </style>
</head>

<body>
    @php($fidaQuotationDate = now()->format('j-M-y'))
    @foreach ($blocks as $block)
        <section
            class="quotation-sheet{{ count($blocks) > 1 && ! $loop->last ? ' quotation-sheet--page-after' : '' }}">
            <img src="{{ asset('images/fida-quotation-letterhead.png') }}" alt="" class="letterhead-img">

            <div class="quotation-subheader">
                <div class="subheader-contact">
                    <div class="contact-line">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#25D366" aria-hidden="true">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-8.452c-.948-2.374-2.883-4.219-5.288-5.167a10.845 10.845 0 00-4.306-.888h-.004c-5.984 0-10.844 4.86-10.846 10.844a10.78 10.78 0 001.412 5.309l-1.502 5.492 5.629-1.477a10.792 10.792 0 005.156 1.316h.004c5.984 0 10.845-4.86 10.847-10.845a10.756 10.756 0 00-2.894-7.594" />
                        </svg>
                        <strong>0300-3634052</strong>
                    </div>
                    <div class="contact-line">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#EA4335"
                                d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0-8 5L4 6h16zm0 12H4V8l8 5 8-5v10z" />
                        </svg>
                        <strong>hfida6232@gmail.com</strong>
                    </div>
                </div>
                <div class="subheader-title">Quotation</div>
                <div class="subheader-date">{{ $fidaQuotationDate }}</div>
            </div>

            <div class="recipient-block">
                <div>To,</div>
                <div>{{ $invoice->client_name }}</div>
                <div>{{ $invoice->project_name }}</div>
            </div>

            <p class="salutation">Dear Sir,</p>
            <p class="subject-line">We would like to quote our best price of {{ trim((string) ($block['item']->name ?? '')) ?: $block['scope'] }}</p>

            <table class="quotation">
                <thead>
                    <tr>
                        <th class="sn">S.No</th>
                        <th class="desc">Description</th>
                        <th class="size">Size</th>
                        <th class="num">Rate</th>
                        <th class="num">App Qty</th>
                        <th class="num">Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($block['summaries'] as $idx => $summary)
                        <tr>
                            <td class="sn">{{ $idx + 1 }}</td>
                            <td class="desc">
                                {{ $summary->invoiceRate->name ?? '' }}
                                @if (!empty($summary->remarks))
                                    <br>{{ $summary->remarks }}
                                @endif
                            </td>
                            <td class="size">{{ $summary->invoiceRate->unit ?? '' }}</td>
                            <td class="num">
                                {{ number_format((float) ($summary->invoiceRate->rate ?? 0), 0, '.', ',') }}
                            </td>
                            <td class="num">{{ number_format((float) $summary->quantity, 0, '.', ',') }}</td>
                            <td class="num">{{ number_format((float) $summary->amount, 0, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="total-label">Total Amount Rs <span class="paren">(Final as per site
                                measurement)</span></td>
                        <td class="num">{{ number_format((float) $block['totalAmount'], 0, '.', ',') }}</td>
                    </tr>
                </tfoot>
            </table>

            <p class="closing">Finally, we trust our offer will meet in line of your requirement and looking forward for
                receiving your valued order at an early date.</p>
            <p class="closing">We hope that this quotation will be meet your requirement with satisfaction</p>

            @if (!empty($block['notes']))
                <div class="notes-heading">Note:</div>
                <ul class="notes-list">
                    @foreach ($block['notes'] as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
    <script>
        window.addEventListener('load', function() {
            window.setTimeout(function() {
                window.print();
            }, 1500);
        });
    </script>
</body>

</html>
