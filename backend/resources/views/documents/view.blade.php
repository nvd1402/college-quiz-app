<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $document->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            -webkit-touch-callout: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }
        body {
            background: #000;
            -webkit-user-drag: none;
            -moz-user-drag: none;
            -ms-user-drag: none;
            user-drag: none;
        }
        * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-user-drag: none !important;
            -moz-user-drag: none !important;
            -ms-user-drag: none !important;
            user-drag: none !important;
        }
        #pdf-container {
            width: 100%;
            height: 100vh;
            position: relative;
        }
        #pdf-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        /* Ẩn khi in */
        @media print {
            body {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div id="pdf-container">
        <iframe 
            id="pdf-iframe"
            src="{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=1" 
            title="{{ $document->title }}"
            allow="fullscreen"
        ></iframe>
    </div>

    <!-- Disable DevTools Library - Đặt ở cuối body theo khuyến nghị -->
    <script 
        disable-devtool-auto 
        src='https://cdn.jsdelivr.net/npm/disable-devtool@latest'
        disable-menu='true'
        disable-select='true'
        disable-copy='true'
        disable-cut='true'
        disable-paste='true'
        clear-log='true'
        detectors='0 1 2 3 4 5 6 7 8'
        interval='200'
    ></script>
    
    <script>
        // Chặn print (bổ sung cho disable-devtool)
        window.addEventListener('beforeprint', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }, true);

        window.addEventListener('afterprint', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }, true);

        // Chặn print từ iframe
        const iframe = document.getElementById('pdf-iframe');
        if (iframe) {
            iframe.addEventListener('load', function() {
                try {
                    if (iframe.contentWindow) {
                        iframe.contentWindow.addEventListener('beforeprint', function(e) {
                            e.preventDefault();
                            return false;
                        });
                    }
                } catch (e) {
                    // Ignore cross-origin errors
                }
            });
        }
    </script>
</body>
</html>

