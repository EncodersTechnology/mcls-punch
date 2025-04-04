<x-app-layout>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: 20px auto;
        }
        h2 {
            text-align: center;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #005f73;
            color: white;
        }
        .section-header {
            background-color: #d1d1d1;
            font-weight: bold;
        }
        .highlight {
            font-weight: bold;
        }
        .monthly {
            background-color: #cce7f0;
        }
        .important {
            color: red;
            font-weight: bold;
        }
        .no-input {
            background-color: #e0e0e0;
        }
    </style>
    <div class="container">
        <table style="background-color:white;">
            <tr class="section-header">
                <td colspan="8">DAY SHIFT CHECKLIST</td>
            </tr>
            <tr>
                <th>TASK</th>
                <th>SUN</th>
                <th>MON</th>
                <th>TUE</th>
                <th>WED</th>
                <th>THU</th>
                <th>FRI</th>
                <th>SAT</th>
            </tr>
            <tr class="section-header">
                <td colspan="8">MEDICATION</td>
            </tr>
            <tr><td>ADMINISTER MEDS AT MEDICATION TIMES</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>COMPLETE MAR SHEETS</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>MEDICATION INTAKE (<span class="important">TUESDAY</span>)</td><td class="no-input""></td><td>✔</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            <tr class="monthly"><td>CHECK FOR EXPIRED MED AND SUPPLIES (<span class="highlight">MONTHLY</span>)</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr class="monthly"><td>MAR SHEET CROSS CHECKED WITH MEDS (<span class="highlight">MONTHLY</span>)</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            
            <tr class="section-header">
                <td colspan="8">INDIVIDUAL SUPPORTS</td>
            </tr>
            <tr><td>HYGIENE (AS NEEDED)</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>REVIEW ACTIVITY SCHEDULE FOR THE DAY</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>COMPLETE THE VISUAL SCHEDULE FOR THE DAY</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>PRIVACY LOCKS ENGAGED</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>DAILY WALKS</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>

            <tr class="section-header">
                <td colspan="8">CLEANING/COMPLIANCE</td>
            </tr>
            <tr><td>EMPTY ALL GARBAGE, RECYCLING & GREEN BIN</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>TAKE OUT GARBAGE</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>CLEAN KITCHEN AFTER MEAL PREP</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>WIPE DOWN TABLES AND CHAIRS</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>CLEAN FLOORS AROUND DINING TABLE</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>LOAD AND UNLOAD DISHWASHER</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>COMPLETE COVID-19 SANITIZING SCHEDULE</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            
            <tr class="section-header">
                <td colspan="8">DATA</td>
            </tr>
            <tr><td>FILL OUT DATA ON CATALYST</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>COMPLETE LOG BOOK (<span class="important">TEMPERATURE CHECKS</span>)</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>UPDATE FAMILIES (AS NEEDED)</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>ORGANIZE BINDERS</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>Staff INITIAL</td><td></td><td>KN</td><td></td><td></td><td></td><td></td><td></td></tr>
        </table>
        
    </div>
    <div class="container">
        <table style="background-color:white;">
            <tr class="section-header">
                <td colspan="8">NIGHT SHIFT CHECKLIST</td>
            </tr>
            <tr>
                <th>TASK</th>
                <th>SUN</th>
                <th>MON</th>
                <th>TUE</th>
                <th>WED</th>
                <th>THU</th>
                <th>FRI</th>
                <th>SAT</th>
            </tr>
            <tr class="section-header">
                <td colspan="8">KITCHEN/COMPLIANCE</td>
            </tr>
            <tr><td>CLEAN REFRIGERATOR (<span class="highlight">WEEKLY</span>)</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td>✔</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            <tr><td>ASSURE THAT FOOD IN FRIDGE IS LABELLED & DATED (<span class="highlight">WEEKLY</span>)</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            <tr><td>CHECK FRIDGE FOR EXPIRED FOOD (<span class="highlight">WEEKLY</span>)</td><td></td><td></td><td></td><td>✔</td><td></td><td></td><td></td></tr>
            <tr><td>UNLOAD DISHWASHER</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>CLEAN MICROWAVE</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td></td></tr>
            <tr><td>CLEAN STOVE (<span class="highlight">WEEKLY</span>)</td><td class="no-input"></td><td class="no-input"></td><td></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            <tr><td>CUPBOARDS ORGANIZE/WIPE DOWN (<span class="highlight">WEEKLY</span>)</td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td><td></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            
            <tr class="section-header">
                <td colspan="8">CLEANING/COMPLIANCE</td>
            </tr>
            <tr><td>LAUNDRY</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>CLEAN BATHROOM (<span class="important">SANITISE SINK, TOILET, SHOWERS</span>)</td><td></td><td></td><td></td><td>✔</td><td></td><td></td><td></td></tr>
            <tr><td>SWEEP/MOP BATHROOM</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>SWEEP/MOP LIVING ROOM AREA</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>SPOT CLEAN ALL WALLS (<span class="highlight">WEEKLY</span>)</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>CLEAN WINDOW LEDGES (<span class="highlight">WEEKLY</span>)</td><td class="no-input"></td><td class="no-input"></td><td></td><td></td><td class="no-input"></td><td class="no-input"></td><td class="no-input"></td></tr>
            <tr><td>CLEAN FRONT AREA</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>WIPE DOWN DOORS</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>SANITIZE ALL DOOR KNOBS</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            
            <tr class="section-header">
                <td colspan="8">DATA</td>
            </tr>
            <tr><td>COMPLETE DATA</td><td></td><td>✔</td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>MAKE SURE AREA IS ORGANIZED (MEDICATION AREA)</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>MAKE A LIST OF NEEDED SUPPLIES</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>Staff INITIAL</td><td></td><td>KN</td><td></td><td></td><td></td><td></td><td></td></tr>
        </table>
    </div>
</x-app-layout>
