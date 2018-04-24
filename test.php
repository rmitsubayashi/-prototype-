<html>
<head>
<link rel="stylesheet" href="style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
//会話データ用
const sentence = "sentence";
const person = "person";
const time = "time";


function parseConversationLine(line){
    var arr = line.split(";");
    var lineSentence = arr[0];
    var linePerson = parseInt(arr[1]);
    var lineTime = parseInt(arr[2]);
    
    return {sentence : lineSentence, person : linePerson, time : lineTime };
}

function getConversation(){
    return <?php 
        $file = fopen("script.txt", "r") or die ("ファイルを開けませんでした");
        $lines = array();
        while (!feof($file)){
            array_push($lines, fgets($file));
        }
        
        echo json_encode($lines);
    ?>
    ;
}

function getPerson(person){
    return "話者"+person;
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
    
    for (var i=0; i<=highestSpeaker; i++){
        var percentage = 100 * speakerTimes[i] / audioDuration;
        addSpeakerTalkingPercentage(i, percentage.toFixed(1));
        
    }
}

function showSpeakerTalkingPercentages(lineObjects){
    //音声がロードしたら計算する
    document.getElementById('audio').onloadedmetadata = function(){calculateSpeakingTime(lineObjects); setConversationTime(); };
}

function addSpeakerTalkingPercentage(speakerNumber, percentage){
    var speakingTalkingPercentageList = $("#speakerTalkingPercentage");
    var li = createPersonTalkingPercentageListItem(speakerNumber, percentage);
    speakingTalkingPercentageList.append(li);
    
}

function setRepresentativeName(){
    var firstSpeaker = getPerson(0);
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
    var firstSpeaker = getPerson(0);
    $("#profileName").text(firstSpeaker);
}



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
    //今のところsetConersationTime()はデータがないので、
    // 音声データが出たときにまとめて計算する
    showSpeakerTalkingPercentages(lineObjects);
    
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
                <source src="englishTest.mp3" type="audio/mpeg">
            </audio>
        </div>
        
        
        <div>発話率</div>
        <ul id="speakerTalkingPercentage"></ul>
        <div>書き起こし</div>
        <ul id="converstationList"></ul>
    </div>
</body>

</html>