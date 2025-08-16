<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Med 7 CBD – Order Invoice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /* -------------------------------------------------
           Global Styles – keep them simple for PDF rendering
        ---------------------------------------------------*/
        * {
            font-size: 12px;
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.4;
        }

        body {
            margin: 0;
            padding: 0;
            color: #333;
        }

        a {
            color: #0099e5;
            text-decoration: none;
        }

        /* -------------------------------------------------
           Header / Branding
        ---------------------------------------------------*/
        .header {
            padding: 20px 0;
        }

        .logo {
            max-height: 80px;
        }

        .title {
            font-size: 26px;
            font-weight: bold;
            color: #0099e5;
            margin: 0;
        }

        .invoice-meta {
            text-align: right;
            line-height: 1.6;
        }

        /* -------------------------------------------------
           Free‑shipping banner
        ---------------------------------------------------*/
        .banner {
            background: #e6f7ff;
            color: #004d73;
            padding: 12px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            border: 1px solid #b3e0ff;
        }

        /* -------------------------------------------------
           Address blocks
        ---------------------------------------------------*/
        .address {
            margin-bottom: 15px;
        }

        .address h3 {
            margin: 0 0 5px;
            font-size: 14px;
            color: #0099e5;
        }

        .address p,
        .address div {
            margin: 0;
            font-size: 12px;
        }

        /* -------------------------------------------------
           Table – product lines
        ---------------------------------------------------*/
        .lines {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .lines-heading th {
            background: #f2f2f2;
            border: 1px solid #ccc;
            padding: 5px 10px;
            text-align: left;
            font-weight: bold;
        }

        .lines-body td {
            border: 1px solid #ededed;
            padding: 5px 10px;
        }

        .lines-footer td {
            border: 1px solid #ededed;
            padding: 10px;
        }

        .lines-footer .label {
            font-weight: bold;
            text-align: right;
        }

        .lines-footer .value {
            text-align: right;
        }

        .summary {
            margin-bottom: 40px;
        }

        .summary td {
            padding: 5px 10px;
        }

        .summary .total td {
            border-top: 1px solid #ccc;
        }

        /* -------------------------------------------------
           Footer
        ---------------------------------------------------*/
        .footer {
            font-size: 10px;
            color: #777;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .footer .contact {
            margin-bottom: 5px;
        }

        .disclaimer {
            margin-top: 15px;
            line-height: 1.3;
        }
    </style>
</head>

<body>
    <div class="content">

        <!-- ========= FREE SHIPPING BANNER ========= -->


        <!-- ========= HEADER – LOGO & INVOICE INFO ========= -->
        <table class="header" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%">
                <img src="data:image/png;base64,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" 
                alt="Med 7 CBD Logo" class="logo">
                </td>
                <td width="50%" class="invoice-meta">
                    <h1 class="title">Med 7</h1>
                    Invoice: <strong>{{ $record->id ?? '—' }}</strong> <br>
                    Created: {{ $record->placed_at ?? now() }}
                </td>
            </tr>
        </table>

        <!-- ========= ADDRESS BLOCKS ========= -->
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="33%" class="address">
                    <h3>Billing</h3>
                    <div>{{ $record->billingAddress->fullName }}</div>
                    @if($record->billingAddress->company_name)
                    <div>{{ $record->billingAddress->company_name }}</div>
                    @endif
                    @if($record->billingAddress->tax_identifier)
                    <div>Tax ID: {{ $record->billingAddress->tax_identifier }}</div>
                    @endif
                    <div>{{ $record->billingAddress->line_one }}</div>
                    @if($record->billingAddress->line_two)
                    <div>{{ $record->billingAddress->line_two }}</div>
                    @endif
                    @if($record->billingAddress->line_three)
                    <div>{{ $record->billingAddress->line_three }}</div>
                    @endif
                    <div>{{ $record->billingAddress->city }},
                        {{ $record->billingAddress->state }}
                        {{ $record->billingAddress->postcode }}
                    </div>
                    <div>{{ $record->billingAddress->country->name }}</div>
                    @if($record->customer?->tax_identifier)
                    <p>Tax Identifier: {{ $record->customer?->tax_identifier }}</p>
                    @endif
                </td>

                <td width="33%" class="address">
                    <h3>Shipping</h3>
                    <div>{{ $record->shippingAddress->fullName }}</div>
                    @if($record->shippingAddress->company_name)
                    <div>{{ $record->shippingAddress->company_name }}</div>
                    @endif
                    @if($record->shippingAddress->tax_identifier)
                    <div>Tax ID: {{ $record->shippingAddress->tax_identifier }}</div>
                    @endif
                    <div>{{ $record->shippingAddress->line_one }}</div>
                    @if($record->shippingAddress->line_two)
                    <div>{{ $record->shippingAddress->line_two }}</div>
                    @endif
                    @if($record->shippingAddress->line_three)
                    <div>{{ $record->shippingAddress->line_three }}</div>
                    @endif
                    <div>{{ $record->shippingAddress->city }},
                        {{ $record->shippingAddress->state }}
                        {{ $record->shippingAddress->postcode }}
                    </div>
                    <div>{{ $record->shippingAddress->country->name }}</div>
                </td>

                <td width="33%" class="address">
                    <!-- Optional extra column – leave empty or add notes -->
                </td>
            </tr>
        </table>

        <!-- ========= PRODUCT LINES ========= -->
        <table class="lines" cellpadding="0" cellspacing="0">
            <thead class="lines-heading">
                <tr>
                    <th width="30%">Product</th>
                    <th width="20%">SKU</th>
                    <th width="8%">Qty</th>
                    <th width="12%">Unit Price</th>
                    <th width="10%">Discount</th>
                    <th width="8%">Tax Rate</th>
                    <th width="8%">Tax Amount</th>
                    <th width="12%">Line Total</th>
                </tr>
            </thead>
            <tbody class="lines-body">
                @foreach($record->physicalLines as $line)
                <tr>
                    <td>
                        {{ $line->description }}<br>
                        {{ $line->option }}
                    </td>
                    <td>{{ $line->identifier }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ $line->unit_price->formatted }}</td>
                    <td>{{ $line->discount_total->formatted }}</td>
                    <td>{{ $line->tax_breakdown->amounts->sum('percentage') }}%</td>
                    <td>{{ $line->tax_total->formatted }}</td>
                    <td>{{ $line->sub_total->formatted }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="lines-footer">
                <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="label"><strong>Sub‑Total</strong></td>
                    <td class="value">{{ $record->sub_total->formatted }}</td>
                </tr>

                @foreach($record->shippingLines as $shipLine)
                <tr>
                    <td colspan="4"></td>
                    <td colspan="3" class="label">
                        <strong>Shipping</strong><br>
                        <small>{{ strip_tags($shipLine->description) }}</small>
                    </td>
                    <td class="value">{{ $shipLine->sub_total->formatted }}</td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="label"><strong>Tax</strong></td>
                    <td class="value">{{ $record->tax_total->formatted }}</td>
                </tr>

                <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="label"><strong>Total</strong></td>
                    <td class="value">{{ $record->total->formatted }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- ========= OPTIONAL ORDER NOTES ========= -->
        @if($record->notes)
        <p><strong>Order Notes</strong><br>
            {{ $record->notes }}
        </p>
        <br>
        @endif

        <!-- ========= FOOTER ========== -->
        <div class="footer">
            <div class="contact">
                <strong>Med 7 CBD</strong> &nbsp;|&nbsp;
                <a href="tel:8015774223">801.577.4223 (4CBD)</a> &nbsp;|&nbsp;
                <a href="mailto:customercare@med7cbd.com">customercare@med7cbd.com</a> &nbsp;|&nbsp;
                <a href="https://www.med7cbd.com">www.med7cbd.com</a>
            </div>

            <div class="disclaimer">
                FDA DISCLAIMER: The statements made regarding these products have not been evaluated by the
                Food and Drug Administration. These products are not intended to diagnose, treat, cure or
                prevent any disease. All information presented here is not meant as a substitute for or
                alternative to information from health‑care practitioners. Please consult your health‑care
                professional before using any product.<br><br>
                California Proposition 65 Warning: This Full Spectrum Hemp Oil may contain trace amounts of THC,
                a chemical known to the State of California to cause reproductive harm. Learn more at
                <a href="https://www.p65warnings.ca.gov">www.p65warnings.ca.gov</a>.
            </div>

            <div style="margin-top:10px; font-size:9px;">
                © {{ date('Y') }} Med 7 CBD – All Rights Reserved
            </div>
        </div>
    </div>
</body>

</html>