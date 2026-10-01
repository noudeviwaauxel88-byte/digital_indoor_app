<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Livraison - DIINC-{{ str_pad($out->id, 4, '0', STR_PAD_LEFT) }}/BL</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .no-print-bar {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background-color: #4f46e5;
            color: white;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }

        .btn-print:hover {
            background-color: #4338ca;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
        }

        .ref-box {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        .info-section {
            margin-bottom: 25px;
            line-height: 1.8;
            font-size: 13px;
        }

        .info-section div {
            display: flex;
        }

        .info-label {
            font-weight: bold;
            width: 90px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 10px 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        .signatures-table {
            width: 100%;
            margin-top: 50px;
            margin-bottom: 80px;
        }

        .signatures-table td {
            width: 50%;
            font-weight: bold;
            font-size: 13px;
            vertical-align: top;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #333;
            border-top: 1px solid #000;
            padding-top: 8px;
            line-height: 1.4;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <span style="font-weight: 500; color: #334155;">Aperçu avant impression du Bon de Livraison</span>
        <button onclick="window.print()" class="btn-print">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">Digital Indoor Inc.</div>
            </td>
            <td class="ref-box">
                REF : DIINC-{{ str_pad($out->id, 4, '0', STR_PAD_LEFT) }}/BL
            </td>
        </tr>
    </table>

    <div class="document-title">BON DE LIVRAISON</div>

    <div class="info-section">
        <div>
            <span class="info-label">DATE :</span>
            <span>{{ $out->movement_date ? \Carbon\Carbon::parse($out->movement_date)->format('d/m/Y') : date('d/m/Y') }}</span>
        </div>
        <div>
            <span class="info-label">CLIENT :</span>
            <span>{{ $out->project->name ?? $out->other_destination ?? 'N/A' }}</span>
        </div>
        <div>
            <span class="info-label">OBJET :</span>
            <span>{{ $out->reason ?? 'FOURNITURE ET LIVRAISON DE MATÉRIEL' }}</span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30%;">DÉSIGNATION</th>
                <th style="width: 40%;">MODÈLE</th>
                <th style="width: 10%; text-align: center;">QTÉ</th>
                <th style="width: 20%;">OBSERVATION</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $out->equipmentItems->groupBy('equipment_id');
            @endphp

            @forelse($groupedItems as $equipmentId => $items)
                @php $firstItem = $items->first(); @endphp
                <tr>
                    <td>
                        <strong>{{ $firstItem->equipment->title ?? 'N/A' }}</strong>
                    </td>
                    <td>
                        {{ $firstItem->equipment->features ?? '—' }}
                        @if($items->pluck('serial_number')->filter()->isNotEmpty())
                            <br><small style="color: #444;">S/N: {{ $items->pluck('serial_number')->implode(', ') }}</small>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $items->count() }}
                    </td>
                    <td>
                        {{ $out->reason ?? '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Aucun équipement enregistré dans cette sortie.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures-table">
        <tr>
            <td style="text-align: left;">DIGITAL INDOOR INC</td>
            <td style="text-align: right;">LE CLIENT</td>
        </tr>
    </table>

    <div class="footer">
        Digital Indoor Inc. &nbsp;|&nbsp; +(225) 22 27 46 76 41 &nbsp;|&nbsp; +(225) 07 08 14 62 27 &nbsp;|&nbsp; Dincinc@gmail.com &nbsp;|&nbsp; digitalindoor.com<br>
        Résidence Ecosys 2 / Cocody Rivera 4 M'Badon &nbsp;—&nbsp; 24 BP 2460 ABIDJAN 24
    </div>

</body>
</html>