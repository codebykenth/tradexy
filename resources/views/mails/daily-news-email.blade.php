<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #1a1a1a; margin-bottom: 8px;">DAILY MACRO INTELLIGENCE REPORT</h1>
    <p style="color: #555; font-size: 0.95em; margin-top: 0;">
        Generated: {{ now()->toDateTimeString() }}
    </p>

    @if(isset($aiAnalysis['gold']))
        @php
            $gold = $aiAnalysis['gold'];
            $d = $gold['data'] ?? [];
        @endphp
        <h2 style="color:#d4af37; margin-bottom: 8px;"><span style="display:inline-block; width:16px; height:16px; border-radius:50%; background-color:#d4af37; margin-right:8px; vertical-align:middle;"></span> GOLD MARKET UPDATE</h2>
        <p style="margin: 4px 0;">
            <strong>Bias:</strong> {{ $gold['bias'] ?? "N/A" }}<br>
            <strong>Confidence:</strong> {{ $gold['confidence'] ?? "N/A" }}<br>
            <strong>24H Direction:</strong> {{ $d['summary']['price_direction_24h'] ?? "N/A" }}<br>
            <strong>7D Direction:</strong> {{ $d['summary']['price_direction_7d'] ?? "N/A" }}
        </p>

        <h3 style="margin: 16px 0 4px;">Main Driver</h3>
        <p style="margin: 4px 0;">
            <strong>{{ $d['key_driver']['theme'] ?? "N/A" }}</strong><br>
            {{ $d['key_driver']['explanation'] ?? "No explanation available." }}
        </p>

        <h3 style="margin: 16px 0 4px;">Market Context</h3>
        <p style="margin: 4px 0;">
            <strong>USD Dynamics:</strong> {{ $d['market_context']['usd_dynamics'] ?? "N/A" }}<br>
            <strong>Real Yields:</strong> {{ $d['market_context']['real_yields'] ?? "N/A" }}<br>
            <strong>Risk Sentiment:</strong> {{ $d['market_context']['risk_sentiment'] ?? "N/A" }}
        </p>

        <h3 style="margin: 16px 0 4px;">Key Risks</h3>
        @if(!empty($d['risk_factors']) && is_array($d['risk_factors']))
            <ul style="margin: 8px 0; padding-left: 20px; list-style-type: disc;">
                @foreach($d['risk_factors'] as $r)
                    @if(is_string($r))
                        <li>{{ $r }}</li>
                    @elseif(is_array($r))
                        <li>
                            @if(!empty($r['theme']))
                                <strong>{{ $r['theme'] }}:</strong>
                            @endif
                            {{ $r['explanation'] ?? '(no details provided)' }}
                        </li>
                    @else
                        <li>{{ print_r($r, true) }}</li>
                    @endif
                @endforeach
            </ul>
        @else
            <p style="color:#777;">No key risks identified.</p>
        @endif

        <h3 style="margin: 16px 0 4px;">Top Source</h3>
        <p style="margin: 4px 0;">
            <strong>{{ $d['top_news_source']['source_name'] ?? "N/A" }}</strong><br>
            Headline: {{ $d['top_news_source']['headline'] ?? "N/A" }}<br>
            Link: <a href="{{ $d['top_news_source']['url'] ?? '#' }}"
                style="color:#0066cc;">{{ $d['top_news_source']['url'] ?? "N/A" }}</a>
        </p>
        <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">
    @endif

    @if(isset($aiAnalysis['crypto']))
        @php
            $crypto = $aiAnalysis['crypto'];
            $d = $crypto['data'] ?? [];
        @endphp
        <h2 style="color:#f7931a; margin-bottom: 8px;"><span style="display:inline-block; width:16px; height:16px; border-radius:50%; background-color:#f7931a; margin-right:8px; vertical-align:middle;"></span> BITCOIN MARKET UPDATE</h2>
        <p style="margin: 4px 0;">
            <strong>Bias:</strong> {{ $crypto['bias'] ?? "N/A" }}<br>
            <strong>Confidence:</strong> {{ $crypto['confidence'] ?? "N/A" }}<br>
            <strong>Trend Direction:</strong> {{ $d['summary']['trend_direction'] ?? "N/A" }}
        </p>

        <h3 style="margin: 16px 0 4px;">Main Driver</h3>
        <p style="margin: 4px 0;">
            <strong>{{ $d['key_driver']['theme'] ?? "N/A" }}</strong><br>
            {{ $d['key_driver']['explanation'] ?? "No explanation available." }}
        </p>

        <h3 style="margin: 16px 0 4px;">Market Context</h3>
        <p style="margin: 4px 0;">
            <strong>Liquidity:</strong> {{ $d['market_context']['liquidity'] ?? "N/A" }}<br>
            <strong>Risk Regime:</strong> {{ $d['market_context']['risk_regime'] ?? "N/A" }}<br>
            <strong>Institutional Flows:</strong> {{ $d['market_context']['institutional_flows'] ?? "N/A" }}
        </p>

        <h3 style="margin: 16px 0 4px;">Key Risks</h3>
        @if(!empty($d['risk_factors']) && is_array($d['risk_factors']))
            <ul style="margin: 8px 0; padding-left: 20px; list-style-type: disc;">
                @foreach($d['risk_factors'] as $r)
                    @if(is_string($r))
                        <li>{{ $r }}</li>
                    @elseif(is_array($r))
                        <li>
                            @if(!empty($r['theme']))
                                <strong>{{ $r['theme'] }}:</strong>
                            @endif
                            {{ $r['explanation'] ?? '(no details provided)' }}
                        </li>
                    @else
                        <li>{{ print_r($r, true) }}</li>
                    @endif
                @endforeach
            </ul>
        @else
            <p style="color:#777;">No key risks identified.</p>
        @endif

        <h3 style="margin: 16px 0 4px;">Top Source</h3>
        <p style="margin: 4px 0;">
            <strong>{{ $d['top_news_source']['source_name'] ?? "N/A" }}</strong><br>
            Headline: {{ $d['top_news_source']['headline'] ?? "N/A" }}<br>
            Link: <a href="{{ $d['top_news_source']['url'] ?? '#' }}"
                style="color:#0066cc;">{{ $d['top_news_source']['url'] ?? "N/A" }}</a>
        </p>
        <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">
    @endif

    <p style="font-size: 0.85em; color: #666; text-align: center;">
        <span style="display:inline-block; background:#fff3cd; color:#856404; font-weight:bold; padding:2px 6px; border-radius:4px; margin-right:4px;">NOTICE</span> This report is AI-generated for informational purposes only.<br>
        Not financial advice. Always Do Your Own Research (DYOR).
    </p>
</body>

</html>