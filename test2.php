<html>
<head>
<link rel="stylesheet" href="style.css">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
//会話データ用
const sentence = "sentence";
const person = "person";
const time = "time";
const classification = "classification";
//7つ
var classificationColors = ["coral", "cornflowerBlue", "aquamarine", "lightPink", "mediumOrchid", "midnightBlue", "saddleBrown" ];
var classificationLabels = ["予算","決裁権","ニーズ","タイムライン","機能","価格","ヒアリング"];

function parseConversationLine(line){
    var arr = line.split(";");
    var lineSentence = arr[0];
    var linePerson = parseInt(arr[1]);
    var lineTime = parseInt(arr[2]);
    var lineClassification = parseInt(arr[3]);
    return {sentence : lineSentence, person : linePerson, time : lineTime, classification: lineClassification };
}

function timeSet(time){
    var arr = time;
    var lineSentence = arr[0];
    var linePerson = parseInt(arr[1]);
    var lineTime = parseInt(arr[2]);
    return {sentence : lineSentence, person : linePerson, time : lineTime };
}


//ファイルの読み込みできなかった（笑）
function getConversation(){
    return <?php
        $file = fopen("script2.txt", "r") or die ("ファイルを開けませんでした");
        $lines = array();
        while (!feof($file)){
            array_push($lines, fgets($file));
        }

        echo json_encode($lines);
    ?>
    ;
}

function getPeopleLabels(){
    return ["セールス", "★お客様"];
}

function getPerson(person){
    var allPeople = getPeopleLabels();
    return allPeople[person];
}

function createConversationListItem(lineObject, order){
    var text = lineObject.sentence;
    var time = lineObject.time;
    var person = getPerson(lineObject.person);
    var listItem = $("<li id=item"+order+" value="+time+" onclick=goToAudioTime("+time+","+order+")>"+
                    "<b>"+person+"</b> "+text+"</li>");

    listItem.css('cursor', 'pointer');

    return listItem;
}

function createPersonTalkingPercentageListItem(speakerNumber, percentage){
    return $("<li>" + getPerson(speakerNumber) + " " + percentage + "%" + "</li>")
}

function goToAudioTime(time, order){
    document.getElementById('audio').currentTime = time;
    highlightLine(order);
    return false;
}

//global
var currentHighlightOrder = -1;

function highlightLine(order){
    if (currentHighlightOrder == order){
        return;
    }
    if (currentHighlightOrder != -1){
        $("#item"+currentHighlightOrder).removeClass("highlighted");
    }
    $("#item"+order).addClass("highlighted");
    currentHighlightOrder = order;

}

function moveConversationHighlightOnStream(lineObjects){
    var currentTime = document.getElementById('audio').currentTime;
    var lineObjectCt = lineObjects.length;
    for (var i=0; i<lineObjectCt; i++){
        var lineObject = lineObjects[i];
        if (lineObject.time > currentTime){
            highlightLine(i);
            return false;
        }
    }
    highlightLine(lineObjectCt);

    return false;
}

function setAudioTimeUpdateListener(lineObjects){
    document.getElementById('audio').ontimeupdate = function(){moveConversationHighlightOnStream(lineObjects)};
}

function calculateSpeakingTime(lineObjects){
    //まずは何人いるか把握する
    var highestSpeaker = -1;
    var lineObjectCt = lineObjects.length;
    for (var i=0; i<lineObjectCt; i++){
        var speaker = lineObjects[i].person;
        if (highestSpeaker < speaker){
            highestSpeaker = speaker;
        }
    }

    var speakerTimes = new Array(highestSpeaker+1);
    speakerTimes.fill(0);
    //割合を計算する
    var audioDuration = document.getElementById('audio').duration;
    for (var i=0; i<lineObjectCt; i++){
        var speaker = lineObjects[i].person;
        var time = i+1 == lineObjectCt ?
            audioDuration - lineObjects[i].time :
            lineObjects[i+1].time - lineObjects[i].time;

        speakerTimes[speaker] = speakerTimes[speaker] + time;
    }

    var chartValues = [];
    chartValues.push(["スピーカー","割合"]);
    for (var i=0; i<=highestSpeaker; i++){
        var percentage = (100 * speakerTimes[i] / audioDuration);
        chartValues.push([getPerson(i), percentage]);
        //addSpeakerTalkingPercentage(i, percentage.toFixed(1));

    }
    
    drawChart(chartValues);
}

function drawChart(chartValues){
    var data = google.visualization.arrayToDataTable(chartValues);

        console.log(chartValues);
        var chart = new google.visualization.PieChart(document.getElementById('speakingTalkingPercentageChart'));

        chart.draw(data);
}

function showSpeakingTime(lineObjects){
    
    google.charts.setOnLoadCallback(function(){calculateSpeakingTime(lineObjects);});
}

function onLoadMetadata(lineObjects){
    //音声がロードしたら計算する
    document.getElementById('audio').onloadedmetadata =
        function(){
<<<<<<< Updated upstream
            showSpeakingTime(lineObjects); setConversationTime(); setClassificationTimeline(lineObjects); 
=======
            calculateSpeakingTime(lineObjects); setConversationTime(); setClassificationTimeline(lineObjects);
>>>>>>> Stashed changes
        };
}

function addSpeakerTalkingPercentage(speakerNumber, percentage){
    var speakingTalkingPercentageList = $("#speakerTalkingPercentage");
    var li = createPersonTalkingPercentageListItem(speakerNumber, percentage);
    speakingTalkingPercentageList.append(li);

}

function setRepresentativeName(){
    var speakers = getPeopleLabels();
    var firstSpeaker = speakers[0];
    $("#conversationRepresentativeName").text(firstSpeaker);
}

function setConversationTime(){
    var audioDuration = document.getElementById('audio').duration.toFixed(0);
    var startTime = "12:00";
    var durationHours = Math.floor(audioDuration / 3600);
    var endHour = 12 + durationHours;
    var durationMinutes = Math.ceil((audioDuration % 3600) / 60);
    var endMinutes = 0 + durationMinutes;
    var endMinutesString = endMinutes < 10 ? "0" + endMinutes : endMinutes;
    var endTime = endHour + ":" + endMinutesString;

    $("#conversationTime").text(startTime + " ~ " + endTime);
}

function loginUser(){
    var speakers = getPeopleLabels();
    var firstSpeaker = speakers[0];
    console.log(firstSpeaker);
    $("#profileName").text(firstSpeaker);
}

function setClassificationTimeline(lineObjects){
    console.log("setting");
    var audioDuration = document.getElementById('audio').duration;
    var lineObjectCt = lineObjects.length;
    var NO_CLASSIFICATION = -2;
    var tempClassification = NO_CLASSIFICATION;
    var tempStart = -1;
    var tempEnd = -1;
    var tempOrder = -1;
    for (var i=0; i<lineObjectCt; i++){
        var lineObject = lineObjects[i];
        //新しいclassificationの始まり
        if (tempClassification == NO_CLASSIFICATION){
            tempClassification = lineObject.classification;
            tempStart = lineObject.time;
            tempOrder = i;
            continue;
        }

        if (tempClassification == lineObject.classification){
            continue;
        } else {
            //-1だったら何も表示しなくてもいい
            if (tempClassification != -1){
                tempEnd =
                    i+1 == lineObjectCt ?
                    audioDuration :
                    lineObjects[i+1].time;
                addClassificationChunk(tempClassification, tempStart, tempEnd, audioDuration, tempOrder);
            }
            tempStart = -1;
            tempEnd = -1;
            tempOrder = -1;
            tempClassification = NO_CLASSIFICATION;

        }
    }
    //最後も埋める
    if (tempClassification != NO_CLASSIFICATION && tempClassification != -1){
        tempEnd = audioDuration;
        addClassificationChunk(tempClassification, tempStart, tempEnd, audioDuration, tempOrder);
    }
}

function addClassificationChunk(classification, start, end, duration, order){
    var startPercentage = (100 * start / duration).toString() + "%";
    var endPercentage = (100 - (100 * end / duration)).toString() + "%";
    var color = classificationColors[classification];
    var label = classificationLabels[classification];
    var chunk = $("<div class='classificationItem tooltip' onclick=goToAudioTime("+start+","+order+")><span class='tooltiptext'>"+label+"</span></div>");
    chunk.css("left",startPercentage);
    chunk.css("right",endPercentage);
    chunk.css("background-color",color);
    chunk.css("cursor","pointer");
    $("#classificationBar").append(chunk);
}

function setClassificationLabels(){
    //just in case
    var classificationCt = classificationLabels.length;
    for (var i=0; i<classificationCt; i++){
        addClassificationLabel(i);
    }

}

function addClassificationLabel(index){
    var label = classificationLabels[index];
    var color = classificationColors[index];

    var text = $("<div class='classificationLegendItem'>"
        +"<div class='classificationLegendColor' style='background-color: "+color+";'></div>"
        +label+"</div>");
    text.css("color",color);

    $("#classificationLegend").append(text);
}

google.charts.load('current', {'packages':['corechart']});

$(document).ready(function(){
    var conversation = getConversation();
    var list = $("#converstationList");
    var lineLength = conversation.length;
    var lineObjects = new Array(lineLength);

    for (var i=0; i<lineLength; i++){
        var line = conversation[i];
          var lineObject = parseConversationLine(line);
        var li = createConversationListItem(lineObject, i+1);
        list.append(li);

        lineObjects[i] = lineObject;
    }

    setAudioTimeUpdateListener(lineObjects);
    setRepresentativeName();
    loginUser();
    //音声データが出るまで待つ。
    //今のところsetConversationTime()はデータがないので、
    // 音声データが出たときにまとめて計算する
    onLoadMetadata(lineObjects);
    setClassificationLabels();

});
</script>
</head>
<body>
    <div id="header">
        <img id="logo" src="pickupon_logo.svg"/>
        <div id="profileName"></div>
    </div>
    <div id="body">
        <div id="conversationProperties">
            <div id="conversationSuccess">成約</div>
            <div id="conversationRepresentativeLabel">担当</div>
            <div id="conversationRepresentativeName"></div>
        </div>
        <div id="conversationDateTime">
            <div id="conversationDate">3月20日</div>
            <div id="conversationTime"></div>

        </div>
        <div id="audioWrapper" align="center">
            <audio controls id="audio">
                <source src="mic2.mp3" type="audio/mpeg">
            </audio>
        </div>
        <div class="featureBlock">
            <h3>構成</h3>
            <div id="classificationBar"></div>
            <div id="classificationLegend"></div>
        </div>

        <div class="featureBlock">
            <h3>発話率</h3>
            <div id="speakingTalkingPercentageChart"></div>
        </div>
        <div class="featureBlock">
            <h3>書き起こし</h3>
            <ul id="converstationList"></ul>
        </div>
    </div>
</body>

</html>
