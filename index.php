<?php
// Get logs directory from environment variable or use default
$logs_dir = getenv('LOGS_DIR') ?: '/var/www/html/logs';
$file_dir = rtrim($logs_dir, '/') . '/*';
if (isset($_GET['fetch'])) {
    header('Content-Type: text/plain');
    $files = glob($file_dir);
    $output = [];
    
    foreach ($files as $file) {
        $lastLine = trim(shell_exec("tail -n 50 " . escapeshellarg($file)));
        if (!empty($lastLine)) {
            $output[] = $lastLine;
        }
    }
    
    echo implode("\n", $output);
    exit;
}

if (isset($_GET['delete'])) {
    shell_exec('rm -f '. $file_dir);
    echo "deleted";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trace Log Viewer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body { 
            font-family: monospace; 
            background: #222; 
            color: #eee; 
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: #333;
            padding: 15px;
            border-bottom: 1px solid #444;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }
        
        .header h2 {
            margin: 0 0 15px 0;
            text-align: center;
            font-size: 1.2em;
        }
        
        #spaceInfo {
            color: #888;
            text-align: center;
            margin: 0 0 15px 0;
            font-size: 0.9em;
        }
        
        .filter-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .input-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        #filterInput {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            background: #444;
            border: 1px solid #555;
            color: #eee;
            border-radius: 6px;
            font-size: 14px;
        }
        
        #filterInput:focus {
            outline: none;
            border-color: #666;
            background: #555;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .button {
            padding: 12px 20px;
            background: #555;
            border: none;
            color: #eee;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            transition: background-color 0.2s;
        }
        
        .button:hover {
            background: #666;
        }
        
        .button:active {
            background: #444;
        }
        
        .delete-btn {
            background: #a00;
        }
        
        .delete-btn:hover {
            background: #c00;
        }
        
        .filter-help {
            color: #888;
            font-size: 0.8em;
            margin: 10px 0 0 0;
            text-align: center;
            line-height: 1.4;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 15px;
            min-height: 0;
        }
        
        #logContainer { 
            flex: 1;
            overflow-y: auto;
            border: 1px solid #444;
            background: #111;
            padding: 15px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.4;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        /* Mobile optimizations - Compact view */
        @media (max-width: 768px) {
            .header {
                padding: 8px;
            }
            
            .header h2 {
                display: none; /* Hide title on mobile */
            }
            
            #spaceInfo {
                display: none; /* Hide profile size info on mobile */
            }
            
            .filter-container {
                margin: 0;
                max-width: none;
            }
            
            .input-row {
                flex-direction: row;
                align-items: center;
                gap: 8px;
            }
            
            #filterInput {
                flex: 1;
                min-width: 0;
                padding: 10px;
                font-size: 14px;
            }
            
            .button-group {
                flex-shrink: 0;
                gap: 8px;
            }
            
            .button {
                padding: 10px 12px;
                font-size: 13px;
                white-space: nowrap;
            }
            
            .filter-help {
                display: none; /* Hide help text on mobile for more compact view */
            }
            
            .main-content {
                padding: 8px;
            }
            
            #logContainer {
                font-size: 12px;
                padding: 10px;
            }
        }
        
        /* Very small mobile devices - Ultra compact */
        @media (max-width: 480px) {
            .header {
                padding: 6px;
            }
            
            .input-row {
                gap: 6px;
            }
            
            #filterInput {
                padding: 8px;
                font-size: 13px;
            }
            
            .button {
                padding: 8px 10px;
                font-size: 12px;
            }
            
            .main-content {
                padding: 6px;
            }
            
            #logContainer {
                font-size: 11px;
                padding: 8px;
            }
        }
        
        /* Extra small devices - Maximum compactness */
        @media (max-width: 360px) {
            .header {
                padding: 4px;
            }
            
            .input-row {
                gap: 4px;
            }
            
            #filterInput {
                padding: 6px;
                font-size: 12px;
            }
            
            .button {
                padding: 6px 8px;
                font-size: 11px;
            }
            
            .main-content {
                padding: 4px;
            }
            
            #logContainer {
                font-size: 10px;
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Trace Log Viewer</h2>
        <div id="spaceInfo">Loading space info...</div>
        <div class="filter-container">
            <div class="input-row">
                <input type="text" id="filterInput" placeholder="Filter logs by string...">
                <div class="button-group">
                    <button class="button" onclick="clearLogs()">Clear Logs</button>
                    <button class="button delete-btn" onclick="deleteProfiles()">Delete Profiles</button>
                </div>
            </div>
            <div class="filter-help">
                Use && for AND, || for OR (e.g., "error && database || warning && server")<br>
                Use -a N to show N lines after match, -b N to show N lines before match (e.g., "error -a 10 -b 5")
            </div>
        </div>
    </div>
    <div class="main-content">
        <pre id="logContainer"></pre>
    </div>
  
    <script>
        const logs = new Set();
        const uLogs = new Set();
        var lineNumber = 1;
        let filterValue = '';

        function updateSpaceInfo() {
            fetch('profiles.php?space=1')
                .then(response => response.text())
                .then(response => {
                    document.getElementById('spaceInfo').textContent = 'Profiles directory size: '+ response;
                });
        }

        function deleteProfiles() {
            if (confirm('Are you sure you want to delete all profile files?')) {
                fetch('index.php?delete=1')
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'deleted') {
                            clearLogs();
                            updateSpaceInfo();
                        }
                    });
            }
        }

        function parseFilterExpression(filterStr) {
            let after = 0;
            let before = 0;
            let searchTerms = filterStr;

            // Extract -a parameter
            const afterMatch = filterStr.match(/-a\s+(\d+)/);
            if (afterMatch) {
                after = parseInt(afterMatch[1]);
                searchTerms = searchTerms.replace(/-a\s+\d+/, '');
            }

            // Extract -b parameter
            const beforeMatch = filterStr.match(/-b\s+(\d+)/);
            if (beforeMatch) {
                before = parseInt(beforeMatch[1]);
                searchTerms = searchTerms.replace(/-b\s+\d+/, '');
            }

            const orParts = searchTerms.trim().split('||').map(p => p.trim());
            return {
                orParts: orParts.map(orPart => {
                    return orPart.split('&&').map(p => p.trim().toLowerCase());
                }),
                after: after,
                before: before
            };
        }

        function matchesFilter(logs, currentIndex, filterExpr) {
            if (!filterExpr.orParts.length || (filterExpr.orParts.length === 1 && !filterExpr.orParts[0][0])) {
                return true;
            }

            const currentLog = logs[currentIndex].toLowerCase();
            
            // Check if the current line matches the filter
            const matches = filterExpr.orParts.some(andGroup => {
                return andGroup.every(term => currentLog.includes(term));
            });

            // If current line matches, always include it
            if (matches) {
                return true;
            }

            // Only look for context if we have -a or -b parameters
            if (filterExpr.after === 0 && filterExpr.before === 0) {
                return false;
            }

            // Look for preceding match for -a parameter
            for (let i = currentIndex - 1; i >= 0; i--) {
                const contextLog = logs[i].toLowerCase();
                if (filterExpr.orParts.some(andGroup => andGroup.every(term => contextLog.includes(term)))) {
                    // If we found a match, check if current line is within the 'after' range
                    const distance = currentIndex - i;
                    if (distance > 0 && distance <= filterExpr.after) {
                        return true;
                    }
                    break; // Stop searching once we find the nearest match
                }
            }

            // Look for following match for -b parameter
            if (filterExpr.before > 0) {
                for (let i = currentIndex + 1; i < logs.length; i++) {
                    const contextLog = logs[i].toLowerCase();
                    if (filterExpr.orParts.some(andGroup => andGroup.every(term => contextLog.includes(term)))) {
                        // If we found a match, check if current line is within the 'before' range
                        const distance = i - currentIndex;
                        if (distance > 0 && distance <= filterExpr.before) {
                            return true;
                        }
                        break; // Stop searching once we find the nearest match
                    }
                }
            }

            return false;
        }


        document.getElementById('filterInput').addEventListener('input', function(e) {
            filterValue = e.target.value;
            refreshDisplay();
        });

        function clearLogs() {
            lineNumber=1;
            logs.clear();
            uLogs.clear();
            document.getElementById('logContainer').textContent = '';
        }

        function refreshDisplay() {
            const logContainer = document.getElementById('logContainer');
            const filterInput = document.getElementById('filterInput');
            logContainer.textContent = '';
            
            const filterExpression = parseFilterExpression(filterInput.value);
            const logsArray = Array.from(logs);
            
            const filteredLogs = logsArray
                .filter((log, index) => matchesFilter(logsArray, index, filterExpression))
                .join('\n');
            
            logContainer.textContent = filteredLogs;
        }

        function fetchLog() {
            fetch('index.php?fetch=1')
                .then(response => response.text())
                .then(data => {
                    if (data.trim().length > 0) {
                        const logContainer = document.getElementById("logContainer");
                        const lines = data.trim().split('\n');
                        let newLogsAdded = false;
                        
                        lines.forEach(line => {
                           // Only add if this exact log line doesn't already exist
                           if (!uLogs.has(line)) {
                               uLogs.add(line);
                               logs.add((lineNumber++) + " " + line);
                               newLogsAdded = true;
                           }
                        });

                        if (newLogsAdded) {
                            refreshDisplay();
                            logContainer.scrollTop = logContainer.scrollHeight;
                        }

                    }
                })
                .catch(err => console.error('Error fetching log:', err));
        }
    
        // Initial calls
        updateSpaceInfo();
        setInterval(updateSpaceInfo, 5000); // Update space info every 5 seconds
        setInterval(fetchLog, 1000);
        fetchLog();
    </script>
</body>
</html>
