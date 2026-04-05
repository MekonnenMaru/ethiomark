<html lang="en" data-qb-installed="true"><head><script>if(sessionStorage.getItem('card_list_402')=='')window.location.href = 'product.php';</script>



    <title>Bingo Caller</title>

    <meta charset="UTF-8">
    <!--link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen">
    <script src="libs/js/jquery.min.js"></script>
    <script>

        sessionStorage.setItem("called", "");
        // Announce the Bingo number        
        
      document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        e.target.click();
      });
        function clear(){            
                document.getElementById('check').innerHTML="Check";
                document.getElementById('card_status').innerHTML="";
                document.getElementById('check_tit').innerHTML="Check Card";
                for (var i = 0; i < 5; i++)
                        for (var j = 0; j < 5; j++){
                            document.getElementById(j+''+i).innerHTML="O";
                            document.getElementById(j+''+i).classList.remove('called');
                            document.getElementById(j+''+i).classList.remove('border');
                            document.getElementById(j+''+i).classList.remove('brad');
                            document.getElementById(j+''+i).style.backgroundColor = "#ffffff";
                        }
                    document.getElementById('22').innerHTML="Free";
        }
        function checkEvent()
        {
            
                    if(document.getElementById('check').innerHTML=="Check"){
                document.getElementById('check_tit').innerHTML="Check Card No. - ".concat(document.getElementById('card').value);
                if($('#cards').val().split(",").indexOf($('#card').val())>=0){
                    var card_num = $('#card').val();

                var data = no_cards[$('#cards').val().split(",").indexOf($('#card').val())];
                
                
                    //alert(data.split("/")[1]);
                    
                    for (var i = 0; i < 5; i++) {
                        for (var j = 0; j < 5; j++) {
                            document.getElementById(j+''+i).innerHTML=data.split("/")[1].split("-")[i].split(",")[j];
                        }                        
                    }
                    document.getElementById('22').innerHTML="Free";
                    if(parseInt(data.split("/")[2])){
                        check(data);
                    }
                    else {
                        document.getElementById('card_status').innerHTML="Locked";
                        document.getElementById('check').innerHTML="Unlock";
                    }
                
                }
            else {document.getElementById('card_status').innerHTML="አልተመዘገበም";
            document.getElementById('card_status').style.color = "red";}
            } else if(document.getElementById('check').innerHTML=="Lock"){
                    var card_num = $('#card').val();
                    for(var i=0;i<no_cards.length-1;i++)
                        if (parseInt(no_cards[i].split("/")[0])===parseInt(card_num))
                            {
                                let chars = no_cards[$('#cards').val().split(",").indexOf($('#card').val())].split("/");
                                chars[2] = 0;
                                no_cards[i] = chars.join('/');
                            }
                        document.getElementById('check').innerHTML="Unlock";
                        document.getElementById('card_status').innerHTML="Locked";                 
                        alert("Cartela has been locked!");                    

                    
            } else if(document.getElementById('check').innerHTML=="Unlock"){
                    var card_num = $('#card').val();
                    for(var i=0;i<no_cards.length-1;i++)
                        if (parseInt(no_cards[i].split("/")[0])===parseInt(card_num))
                            {
                                let chars = no_cards[$('#cards').val().split(",").indexOf($('#card').val())].split("/");
                                chars[2] = 1;
                                no_cards[i] = chars.join('/');
                            }
                        //alert(no_cards[$('#cards').val().split(",").indexOf($('#card').val())].split("/")[2]);
                        document.getElementById('check').innerHTML="Check";
                        document.getElementById('card_status').innerHTML="Unocked";
                        alert("Cartela has been Unlocked!");                    
            }
                    document.getElementById('card').blur();
                    document.getElementById('check').blur();
        }
        function handleEnterKey(event) {
            if (event.key === "Enter") { // Check if the Enter key was pressed
                checkEvent(); // Call the desired function
                document.getElementById('card').blur();
                document.getElementById('check').blur();
            }
        }
        $(document).ready(() => {
            $("#card").keyup(function(){
            clear();
          });

            $('#show').click(function(){
                if(sessionStorage.getItem("called").split(",").length>=4){
                const res = $('#check_div');
                    res.show(300);
                    document.getElementById("card").focus();
                }
                else event.preventDefault();
            })

            $('#hide').click(function(){
                document.getElementById('card').value="";
                clear();
                const res = $('#check_div');
                    res.hide(300);
            })

            $('#check').click(function(){
                checkEvent();
    })

});

    </script>

<script type="text/javascript">
  function check($data)
  {
            let $counter=0;
            let clearchar = sessionStorage.getItem("called").replaceAll("B", "");
            clearchar = clearchar.replaceAll("I", "");
            clearchar = clearchar.replaceAll("N", "");
            clearchar = clearchar.replaceAll("G", "");
            clearchar = clearchar.replaceAll("O", "");

            //clearchar = "18,27,65,52,56,59";

            let separatedArray = clearchar.split(",");
            
                    document.getElementById('22').classList.add('called');
                    document.getElementById('22').classList.add('colorcall');
                    document.getElementById('22').classList.add('brad');
                    document.getElementById('22').style.backgroundColor = "#fc7e03";

                $indx = "",allindx="",winindx="",wincnt=0;

                if(sessionStorage.getItem("checking_402").indexOf("h")!==-1)
                for (var i = 0; i < 5; i++){
                    allindx="";
                    $counter=0;
                    for (var j = 0; j < 5; j++){
                    $indx = i.toString().concat(j.toString());
                    if(document.getElementById($indx).innerHTML==="Free"){
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;}
                    else if(separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())>=0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;
                    }         
                    }
                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }   
                    }

                if(sessionStorage.getItem("checking_402").indexOf("v")!==-1)                     
                for (var i = 0; i < 5; i++){
                            //alert($counter);
                        allindx="";
                    $counter=0;
                    for (var j = 0; j < 5; j++){
                    $indx = j.toString().concat(i.toString());
                    if(document.getElementById($indx).innerHTML==="Free"){
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;}
                    else if(separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())>=0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;
                        
                    }
                    }
                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }   
                }
                if(sessionStorage.getItem("checking_402").indexOf("x")!==-1) {
                        allindx="";
                    $counter=0;
                        for (var i = 0; i < 5; i++){
                    $indx = i.toString().concat(i.toString());
                    if(document.getElementById($indx).innerHTML==="Free"){
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;}
                    else if(separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())>=0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;
                    }
                }

                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }   
                        allindx="";
                    $counter=0;
                    var j=0;
                        for (var i = 0; i < 5; i++){
                            j = 4 - i;
                    $indx = i.toString().concat(j.toString());
                    if(document.getElementById($indx).innerHTML==="Free"){
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;} 
                    else if(separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())>=0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                        allindx = allindx.concat($indx).concat(',');
                        $counter = $counter + 1;
                    } 
                    }
                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }
            }
                if(sessionStorage.getItem("checking_402").indexOf("d")!==-1){
                        allindx="";
                    $counter=1;
                    $indx = "00";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }

                    $indx = "40";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    $indx = "04";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    $indx = "44";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    
                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }
            } 
                if(sessionStorage.getItem("checking_402").indexOf("i")!==-1){
                        allindx="";
                    $counter=1;
                    $indx = "11";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }

                    $indx = "31";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    $indx = "33";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    $indx = "13";
                    if(separatedArray.indexOf(document.getElementById($indx).innerHTML) >= 0 && parseInt(separatedArray[separatedArray.indexOf(document.getElementById($indx).innerHTML.toString())])==parseInt(document.getElementById($indx).innerHTML)){
                        document.getElementById($indx).classList.add('called');
                        document.getElementById($indx).classList.add('colorcall');
                        document.getElementById($indx).classList.add('brad');
                    document.getElementById($indx).style.backgroundColor = "#fc7e03";
                    allindx = allindx.concat($indx).concat(',');
                    $counter = $counter + 1;
                    }
                    
                    if($counter >= 5){
                        wincnt = wincnt + 1;
                        winindx = winindx.concat(allindx);
                }
            } 
                if (wincnt>=1) 
                {
                        let separatedArray1 = winindx.split(",");
                        for (var i = 0; i < separatedArray1.length-1 ;  i++) {
                            document.getElementById(separatedArray1[i]).style.backgroundColor = "#1ebb00";
                            document.getElementById(separatedArray1[i]).classList.remove('brad');
                        }
                        if(wincnt>=parseInt(sessionStorage.getItem("zg_402"))){
                        document.getElementById('check').innerHTML="Lock";
                        document.getElementById('card_status').innerHTML="አሸንፏል";
                            document.getElementById('card_status').style.color = "#1ebb00";
                            const soundName = "fireworks.mp3";
                            if (soundName) {
                                    const transaction = db.transaction(['sounds'], 'readonly');
                                    const objectStore = transaction.objectStore('sounds');
                                    const getRequest = objectStore.get(soundName);

                                    getRequest.onsuccess = (event) => {
                                        const fileRecord = event.target.result;

                                        if (fileRecord) {
                                            const audio = new Audio(URL.createObjectURL(fileRecord.file));
                                            audio.play();
                                        } else {
                                            alert('Sound not found.');
                                        }
                                    };

                                    getRequest.onerror = (event) => {
                                        console.error('Error retrieving sound:', event.target.errorCode);
                                    };
                                } else alert('Please enter a sound name.');
                                
                        celebrate();
                        }else {
                            document.getElementById('check').innerHTML="Lock";
                            document.getElementById('card_status').innerHTML="አላሸነፈም";
                            document.getElementById('card_status').style.color = "#fc7e03";
                        }
            }
            
                else {
                    document.getElementById('check').innerHTML="Lock";
                    document.getElementById('card_status').innerHTML="አላሸነፈም";
                    document.getElementById('card_status').style.color = "#fc7e03";
            }
                document.getElementById('card').blur();
                document.getElementById('check').blur();
    }
</script>
    <link rel="stylesheet" href="css/main.css">
    <style type="text/css">
        /* Typography */
*{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    font-family: "Poppins",sans-serif;

}
body { color: #444; margin: 10px;background-color: #073567;}
html, body, p, .btn { font-size: 1rem; }
h1 { font-size: 2.5rem; margin: 0; font-weight: bold; text-transform: uppercase; }
h2 { font-size: 2rem; }
h3 { font-size: 1rem; margin: 0; padding: 0.5em; text-align: center; background: #3d3d3d; color: #fff; }
h4 { font-size: 1rem; margin: 0; }
#callNumber span { font-weight: bold; }
header img {
    max-width: 12rem;
    height: auto;
}
#voices {
    text-align: right;
}
#voices .btn {
    font-size: 0.7rem;
    padding: 0.5em 1em;
    line-height: 1.5;
    height: auto;
    margin: 0 0.5rem 0 0;
}
#voices i {
    vertical-align: middle;
    margin-right: 1rem;
}
.error {
    color: red;
    font-size: 0.9rem;
}
.icon {
    margin: 1.25rem 1%;
}
.icon img {
    height: 4rem;
    max-width: 100%;
}
#delayrange form {
    width: 67%;
    margin-right: 2%;
}
#delayrange i, #delayrange form {
    display: inline-block;
    vertical-align: middle;
}
#delayrange i {
    margin: 0.5em 0.5em 0 0;
}
#range {
    direction: rtl;
}
input[type=range]+.thumb {
    background-color: #26c6da;
}
input[type=range]+.thumb.active .value {
    font-size: 14px;
}

/* Spacing */
body { padding: 0;}
.btn { margin: 0.25em; }
header { padding: 2rem 1.5rem 0; }
header .row:not(:last-of-type) {
    margin-bottom: 0;
}
.row_current_ball{width: 24%;height: 400px; float: left;margin: 10px;}
section, .board { padding: 0 1.5rem; }
footer { padding: 1rem 1.5rem 0.5rem; }
.addthis_inline_share_toolbox {
    clear: none !important;
    display: inline-block;
    vertical-align: middle;
}
.letter-block:first-of-type > div {
    padding-top: 0.5rem;
}
.letter-block:last-of-type > div {
    padding-bottom: 0.5rem;
}

#check_div{
position: absolute;
top: 48%;
border: 1px solid black;
border-top-right-radius: 5%;
width: 40%;
height: 333px;
padding: 10px;
float: left;
margin-left: -10px;
background: #ffffff; 
display:none;
z-index: 1;
}
#check_div #check_tit{
    width: 78%;
    position:relative;
    float:left;
    font-style:time new roman;
    color: #0000FF;
}

#check_div table{
    position:relative;
    float:left;
    width: 50%;
    margin-top: 0px;
    //margin-left: 280px;
}
#check_div #card_status{
    position: relative;
    float: left;
    margin-top: 20px;
    margin-left: 50px;
    font-style:time new roman;
}

#check_div #check{
    position:relative;
    width:190px;
    margin-top: 2px;
    padding-left:35px;
    float:left;
}

#check_div #card{
    float: left;
    position: relative;
    margin-top: 10px;
    box-sizing: border-box;
    text-align: center;
    font-family: inherit;
    font-size: 22px;
    vertical-align: baseline;
    font-weight: 400;
    line-height: 1.29;
    letter-spacing: .16px;
    border-radius: 0;
    outline: 2px solid transparent;
    outline-offset: -2px;
    width: 190px;
    height: 40px;
    border: none;
    border-bottom: 1px solid #8d8d8d;
    background-color: #f4f4f4;
    padding: 0 6px;
    color: #161616;
    transition: background-color 70ms cubic-bezier(.2,0,.38,.9),outline 70ms cubic-bezier(.2,0,.38,.9);  
    :focus{
    outline: 2px solid #0f62fe;
    outline-offset: -2px;
    }
}
#check_div #hide{
    float: right;
    height: 40px; 
    width: 40px;
    padding: 0 12px; 
    margin: 5px; 
    background: black; 
    border-radius: 50%;
    color: white;
    cursor: default;
}

/* Buttons */
#buttons a {
    margin: 0 1% 1%;
    font-weight: bold;
}
.btn-title{
background-image: linear-gradient(to right, #667eea, #764ba2, #6B8DD6, #8E37D7);
    box-shadow: 0 4px 15px 0 rgba(116, 79, 168, 0.75);
}

.btn-value{
    background-image: linear-gradient(to right, #111111, #111111, #111111, #111111);
    box-shadow: 0 4px 15px 0 rgba(252, 104, 110, 0.75);
    border: 7px solid #ed740b;
    height: 50px;
    color: white;
    font-weight: bold;
}

/* Sizing */
#bingoboard { width: 100%; }
.letter, .ball { width: 6.25%; padding: 0.1rem; font-weight: bold; font-size:43px; border:5px solid black; }

.opacit{opacity: 0.3;}
.border{border: 5px solid #ed740b;}
.brad{border-radius: 30%;}

/* Bingo Board */
.ball {
    transition: all ease 1s;
    -webkit-transition: all ease 1s;
    color: black;
    background-color: white;
}
.called, .lastCall {
    font-weight: bold;;
    color: blue;
    opacity: 1;
}
.lastCall {
    animation: blink 2.5s infinite;
    -webkit-animation: blink 2s infinite
}
.colorcall{color: black;}
@keyframes blink {
    0% { color: white; }
    50% { color: #444; }
    100% { color: white; }
}

/* Balls */
#ballGraphic, #ballText {
    padding: 0.8em 1.3em;
    margin: 0.5em auto;
    border-radius: 100%;
}
#ballText {
    line-height: 1;
    padding: 0.5em 1em;
    font-weight: bold;
    font-size: 43px;
}
#currentBall {
    background: #ececec;
    min-height: 12rem;
    position: relative;
    width: 315px;
    height: 350px;
}
#ballText:not(:empty) {
    border: 1px solid red;
    box-shadow: inset -0.5rem -0.5rem 2rem 0 rgba(0, 0, 0, 0.16), 0 0 0 0.25rem white;
    background: radial-gradient(circle at 20% 20%, white, #efefef);
}
#ballText.single {
    padding: 0.5em 1.2em;
}
#callNumber:not(:empty) {
    position: absolute;
    bottom: 0em;
    right: 0em;
    width: 100%;
    height: 60px;
    background: white;
    color: #444;
    text-align: center;
    font-size: 2.2rem;
    font-weight: bold;
    padding: 0.2em;
}
#ballGraphic.blue, #ballGraphic.red, #ballGraphic.white, #ballGraphic.green, #ballGraphic.orange {
    width: 250px;
    height: 250px;
    margin-top: -0.5rem;
    position: relative;
    box-shadow: inset 0.5rem 0.5rem 1.5rem 0.25rem rgba(255,255,255,0.3), inset -0.25rem -0.25rem 1.5rem 0.5rem rgba(0,0,0,0.4);
}

#ballGraphic.blue:after, #ballGraphic.red:after,
#ballGraphic.white:after, #ballGraphic.green:after, #ballGraphic.orange:after {
    content: '';
    background: radial-gradient(circle at 50% -150%, rgba(0,0,0,0.4), transparent);
    height: 0.75rem;
    position: absolute;
    bottom: -1rem;
    width: 70%;
    left: 17%;
    border-radius: 100%;
    margin: 0 auto;
}
/* Ball Colors */
#ballGraphic.blue {
    background: radial-gradient(circle at 20% 20%, #0000FF, #0000ad);
}
#ballGraphic.blue #ballText {
    border: 0.15rem solid #0000FF;
}
#ballGraphic.red {
    background: radial-gradient(circle at 20% 20%, #FF0000, #910000);
}
#ballGraphic.red #ballText {
    border: 0.15rem solid #FF0000;
}
#ballGraphic.white {
    background: radial-gradient(circle at 20% 20%, #ffffff, #b6b6b6);
}
#ballGraphic.white #ballText {
    border: 0.15rem solid #FF0000;
}
#ballGraphic.green {
    background: radial-gradient(circle at 20% 20%, #008000, #004a00);
}
#ballGraphic.green #ballText {
    border: 0.15rem solid #008000;
}
#ballGraphic.orange {
    background: radial-gradient(circle at 20% 20%, #FFA500, #9c6400);
}
#ballGraphic.orange #ballText {
    border: 0.15rem solid #FFA500;
}

/* Footer */
footer {
    background: #f3f3f3;
}
footer p {
    font-size: 0.75rem;
}

#btn{
    position: absolute;
    bottom: 0;
    right: 0;
    float: right;
    margin: 0px;
    padding: 5px;
    border: none;
    background-color: #fe0067;
    color: #ffffff;
    cursor: pointer;
    border-radius: 5px;
}

    </style>
    <style type="text/css">
    h1, th {
  font-family: Georgia, "Times New Roman", Times, serif;
}

h1 {
  font-size: 28px;
}

table {
  border-collapse: collapse;
  margin: 15px;
  width: 80%;
}

th, td {
  width: 30px;
  padding: 5px;
  border: 2px #666 solid;
  text-align: center;
  font-size: 20px;
  cursor: default;
}

#free {
  background-color: #F66;
}
  </style>
    <script type="text/javascript">
        sessionStorage.setItem("voice", sessionStorage.getItem('setting_402').split(",")[0]);
    </script>
<style type="text/css">.lf-progress {
  -webkit-appearance: none;
  -moz-apperance: none;
  width: 100%;
  /* margin: 0 10px; */
  height: 4px;
  border-radius: 3px;
  cursor: pointer;
}
.lf-progress:focus {
  outline: none;
  border: none;
}
.lf-progress::-moz-range-track {
  cursor: pointer;
  background: none;
  border: none;
  outline: none;
}
.lf-progress::-webkit-slider-thumb {
  -webkit-appearance: none !important;
  height: 13px;
  width: 13px;
  border: 0;
  border-radius: 50%;
  background: #0fccce;
  cursor: pointer;
}
.lf-progress::-moz-range-thumb {
  -moz-appearance: none !important;
  height: 13px;
  width: 13px;
  border: 0;
  border-radius: 50%;
  background: #0fccce;
  cursor: pointer;
}
.lf-progress::-ms-track {
  width: 100%;
  height: 3px;
  cursor: pointer;
  background: transparent;
  border-color: transparent;
  color: transparent;
}
.lf-progress::-ms-fill-lower {
  background: #ccc;
  border-radius: 3px;
}
.lf-progress::-ms-fill-upper {
  background: #ccc;
  border-radius: 3px;
}
.lf-progress::-ms-thumb {
  border: 0;
  height: 15px;
  width: 15px;
  border-radius: 50%;
  background: #0fccce;
  cursor: pointer;
}
.lf-progress:focus::-ms-fill-lower {
  background: #ccc;
}
.lf-progress:focus::-ms-fill-upper {
  background: #ccc;
}
.lf-player-container :focus {
  outline: 0;
}
.lf-popover {
  position: relative;
}

.lf-popover-content {
  display: inline-block;
  position: absolute;
  opacity: 1;
  visibility: visible;
  transform: translate(0, -10px);
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
  transition: all 0.3s cubic-bezier(0.75, -0.02, 0.2, 0.97);
}

.lf-popover-content.hidden {
  opacity: 0;
  visibility: hidden;
  transform: translate(0, 0px);
}

.lf-player-btn-container {
  display: flex;
  align-items: center;
}
.lf-player-btn {
  cursor: pointer;
  fill: #999;
  width: 14px;
}

.lf-player-btn.active {
  fill: #555;
}

.lf-popover {
  position: relative;
}

.lf-popover-content {
  display: inline-block;
  position: absolute;
  background-color: #ffffff;
  opacity: 1;

  transform: translate(0, -10px);
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
  transition: all 0.3s cubic-bezier(0.75, -0.02, 0.2, 0.97);
  padding: 10px;
}

.lf-popover-content.hidden {
  opacity: 0;
  visibility: hidden;
  transform: translate(0, 0px);
}

.lf-arrow {
  position: absolute;
  z-index: -1;
  content: '';
  bottom: -9px;
  border-style: solid;
  border-width: 10px 10px 0px 10px;
}

.lf-left-align,
.lf-left-align .lfarrow {
  left: 0;
  right: unset;
}

.lf-right-align,
.lf-right-align .lf-arrow {
  right: 0;
  left: unset;
}

.lf-text-input {
  border: 1px #ccc solid;
  border-radius: 5px;
  padding: 3px;
  width: 60px;
  margin: 0;
}

.lf-color-picker {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  height: 90px;
}

.lf-color-selectors {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.lf-color-component {
  display: flex;
  flex-direction: row;
  font-size: 12px;
  align-items: center;
  justify-content: center;
}

.lf-color-component strong {
  width: 40px;
}

.lf-color-component input[type='range'] {
  margin: 0 0 0 10px;
}

.lf-color-component input[type='number'] {
  width: 50px;
  margin: 0 0 0 10px;
}

.lf-color-preview {
  font-size: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  padding-left: 5px;
}

.lf-preview {
  height: 60px;
  width: 60px;
}

.lf-popover-snapshot {
  width: 150px;
}
.lf-popover-snapshot h5 {
  margin: 5px 0 10px 0;
  font-size: 0.75rem;
}
.lf-popover-snapshot a {
  display: block;
  text-decoration: none;
}
.lf-popover-snapshot a:before {
  content: '⥼';
  margin-right: 5px;
}
.lf-popover-snapshot .lf-note {
  display: block;
  margin-top: 10px;
  color: #999;
}
.lf-player-controls > div {
  margin-right: 5px;
  margin-left: 5px;
}
.lf-player-controls > div:first-child {
  margin-left: 0px;
}
.lf-player-controls > div:last-child {
  margin-right: 0px;
}
</style></head>

<body class="flow-text">    
            <div class="bounce-container" id="bounceContainer"></div>
    <div class="row_current_ball">
        <p id="loadingMessage" style="display: none;">Loading sounds... <span id="progressCount">0</span> / 80</p>
    
        <div class="col s12 m3" style="width: 80%;height: 400px;">
            <h3 style="height:45px;font-weight: bold;font-size: 21px;">Current Call</h3>
            <div id="currentBall" class="valign-wrapper" style="width: 100%;padding-top: 0;"><div id="ballGraphic" style="margin-top:-55px;" class="valign-wrapper red"><span id="ballText" class="valign center-align I24">I<br>24</span></div><span id="callNumber">46 / 75</span></div>
        </div>
    </div>

    <div class="row" style="width: 78%;position: absolute; right: 10px;margin: 10px 0px;padding: 0 0;">
        <div class="col s12" style="padding: 0 0;">
            <div class="center-align grey darken-4 grey-text text-darken-2" id="bingoboard"><div class="letter-block valign-wrapper "><div class="letter valign red darken-1 white-text ">B</div><div class="ball valign opacit B1" id="B1">1</div><div class="ball valign opacit B2 called border brad" id="B2">2</div><div class="ball valign opacit B3 called border brad" id="B3">3</div><div class="ball valign opacit B4 called border brad" id="B4">4</div><div class="ball valign opacit B5 called border brad" id="B5">5</div><div class="ball valign opacit B6 called border brad" id="B6">6</div><div class="ball valign opacit B7" id="B7">7</div><div class="ball valign opacit B8" id="B8">8</div><div class="ball valign opacit B9" id="B9">9</div><div class="ball valign opacit B10" id="B10">10</div><div class="ball valign opacit B11 called border brad" id="B11">11</div><div class="ball valign opacit B12 called border brad" id="B12">12</div><div class="ball valign opacit B13" id="B13">13</div><div class="ball valign opacit B14 called border brad" id="B14">14</div><div class="ball valign opacit B15 called border brad" id="B15">15</div></div><div class="letter-block valign-wrapper "><div class="letter valign red darken-1 white-text ">I</div><div class="ball valign opacit I16 called border brad" id="I16">16</div><div class="ball valign opacit I17 called border brad" id="I17">17</div><div class="ball valign opacit I18 called border brad" id="I18">18</div><div class="ball valign opacit I19 called border brad" id="I19">19</div><div class="ball valign opacit I20" id="I20">20</div><div class="ball valign opacit I21 called border brad" id="I21">21</div><div class="ball valign opacit I22 called border brad" id="I22">22</div><div class="ball valign opacit I23" id="I23">23</div><div class="ball valign opacit I24 lastCall" id="I24">24</div><div class="ball valign opacit I25" id="I25">25</div><div class="ball valign opacit I26 called border brad" id="I26">26</div><div class="ball valign opacit I27 called border brad" id="I27">27</div><div class="ball valign opacit I28 called border brad" id="I28">28</div><div class="ball valign opacit I29 called border brad" id="I29">29</div><div class="ball valign opacit I30 called border brad" id="I30">30</div></div><div class="letter-block valign-wrapper "><div class="letter valign red darken-1 white-text ">N</div><div class="ball valign opacit N31 called border brad" id="N31">31</div><div class="ball valign opacit N32" id="N32">32</div><div class="ball valign opacit N33 called border brad" id="N33">33</div><div class="ball valign opacit N34 called border brad" id="N34">34</div><div class="ball valign opacit N35 called border brad" id="N35">35</div><div class="ball valign opacit N36" id="N36">36</div><div class="ball valign opacit N37" id="N37">37</div><div class="ball valign opacit N38" id="N38">38</div><div class="ball valign opacit N39 called border brad" id="N39">39</div><div class="ball valign opacit N40" id="N40">40</div><div class="ball valign opacit N41" id="N41">41</div><div class="ball valign opacit N42 called border brad" id="N42">42</div><div class="ball valign opacit N43 called border brad" id="N43">43</div><div class="ball valign opacit N44 called border brad" id="N44">44</div><div class="ball valign opacit N45 called border brad" id="N45">45</div></div><div class="letter-block valign-wrapper "><div class="letter valign red darken-1 white-text ">G</div><div class="ball valign opacit G46 called border brad" id="G46">46</div><div class="ball valign opacit G47" id="G47">47</div><div class="ball valign opacit G48 called border brad" id="G48">48</div><div class="ball valign opacit G49 called border brad" id="G49">49</div><div class="ball valign opacit G50 called border brad" id="G50">50</div><div class="ball valign opacit G51" id="G51">51</div><div class="ball valign opacit G52 called border brad" id="G52">52</div><div class="ball valign opacit G53" id="G53">53</div><div class="ball valign opacit G54 called border brad" id="G54">54</div><div class="ball valign opacit G55 called border brad" id="G55">55</div><div class="ball valign opacit G56" id="G56">56</div><div class="ball valign opacit G57" id="G57">57</div><div class="ball valign opacit G58 called border brad" id="G58">58</div><div class="ball valign opacit G59 called border brad" id="G59">59</div><div class="ball valign opacit G60 called border brad" id="G60">60</div></div><div class="letter-block valign-wrapper "><div class="letter valign red darken-1 white-text ">O</div><div class="ball valign opacit O61" id="O61">61</div><div class="ball valign opacit O62 called border brad" id="O62">62</div><div class="ball valign opacit O63 called border brad" id="O63">63</div><div class="ball valign opacit O64 called border brad" id="O64">64</div><div class="ball valign opacit O65" id="O65">65</div><div class="ball valign opacit O66" id="O66">66</div><div class="ball valign opacit O67 called border brad" id="O67">67</div><div class="ball valign opacit O68" id="O68">68</div><div class="ball valign opacit O69" id="O69">69</div><div class="ball valign opacit O70 called border brad" id="O70">70</div><div class="ball valign opacit O71" id="O71">71</div><div class="ball valign opacit O72" id="O72">72</div><div class="ball valign opacit O73" id="O73">73</div><div class="ball valign opacit O74 called border brad" id="O74">74</div><div class="ball valign opacit O75" id="O75">75</div></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m6">
            <div id="delayrange">
                <form action="#" style="display: none;">
                    <p class="range-field">
                        <input type="range" id="range" value="4" min="1" max="16"><span class="thumb"><span class="value"></span></span><span class="thumb"><span class="value"></span></span>
                        <input type="text" id="cards">
                    </p>
                </form>
            </div>
        </div>
    </div>
        <div class="col s12 m9">
            <div class="row">
                <div class="col s12">
                    <div style="display: flex;flex-direction: column;-webkit-box-pack: center;text-align: center;color: white;float: right;margin: -20px 150px 0 0;height: 150px;">WINNER PRIZE<h1 style="font-size: 76px;font-family: sans-serif;" id="prizes">10.00</h1><h2 style="margin-top:0;">Birr</h2></div>
                    <div id="buttons">
                        <a class="btn orange waves-effect disabled btn-value" style="font-size: 1.8rem;  font-weight: bold;display: none;" id="prize">BIRR:- 10.00</a>
                        <a class="btn orange waves-effect disabled btn-value" style="font-size: 1.8rem;  font-weight: bold;" id="price">BET:- 10.00</a>
                    </div>
                </div>
            </div>
            <div class="row" style="position: absolute;bottom: 0; width: 75%;margin-bottom: 0;margin-left: 300px;">
                <div class="col s12">
                    <div id="buttons">
                        <a class="btn cyan waves-effect disabled" id="shuffle" onclick="shuffle()">Shuffle</a>
                        <a class="btn cyan waves-effect" id="resumeGame">Play</a>
                        <a class="btn orange waves-effect disabled" id="pauseGame">Pause<div class="waves-ripple " data-hold="1745006759903" data-scale="scale(12)" data-x="62.71875" data-y="14.171875" style="top:14.171875px;left:62.71875px;-webkit-transform:scale(12);-moz-transform:scale(12);-ms-transform:scale(12);-o-transform:scale(12);transform:scale(12);opacity:1;-webkit-transition-duration:750ms;-moz-transition-duration:750ms;-o-transition-duration:750ms;transition-duration:750ms;-webkit-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-moz-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-o-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);"></div><div class="waves-ripple " data-hold="1745006938406" data-scale="scale(12)" data-x="43.71875" data-y="23.171875" style="top:23.171875px;left:43.71875px;-webkit-transform:scale(12);-moz-transform:scale(12);-ms-transform:scale(12);-o-transform:scale(12);transform:scale(12);opacity:1;-webkit-transition-duration:750ms;-moz-transition-duration:750ms;-o-transition-duration:750ms;transition-duration:750ms;-webkit-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-moz-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-o-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);"></div></a>
                        <a class="btn cyan waves-effect" onclick="check_called()" id="show">Check<div class="waves-ripple " data-hold="1745006694322" data-scale="scale(12.3)" data-x="35.46875" data-y="35.171875" style="top:35.171875px;left:35.46875px;-webkit-transform:scale(12.3);-moz-transform:scale(12.3);-ms-transform:scale(12.3);-o-transform:scale(12.3);transform:scale(12.3);opacity:1;-webkit-transition-duration:750ms;-moz-transition-duration:750ms;-o-transition-duration:750ms;transition-duration:750ms;-webkit-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-moz-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-o-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);"></div><div class="waves-ripple " data-hold="1745006904289" data-scale="scale(12.3)" data-x="56.46875" data-y="20.171875" style="top:20.171875px;left:56.46875px;-webkit-transform:scale(12.3);-moz-transform:scale(12.3);-ms-transform:scale(12.3);-o-transform:scale(12.3);transform:scale(12.3);opacity:1;-webkit-transition-duration:750ms;-moz-transition-duration:750ms;-o-transition-duration:750ms;transition-duration:750ms;-webkit-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-moz-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);-o-transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);transition-timing-function:cubic-bezier(0.250, 0.460, 0.450, 0.940);"></div></a>
                        <a class="btn red waves-effect" id="resetGame">Reset</a>
                    </div>
                </div>
            </div>
        </div>
        <button id="btn">Go Fullscreen</button>

        <div id="check_div" style="display: block;">
            <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span id="check_tit" style="margin-left: 5px;">Check Card No. - 1</span>
            <span id="hide">×</span>
         </strong>
            
            <table>
            <tbody><tr>
              <th class="letter valign red darken-1 white-text " width="20%">B</th>
              <th class="letter valign red darken-1 white-text " width="20%">I</th>
              <th class="letter valign red darken-1 white-text " width="20%">N</th>
              <th class="letter valign red darken-1 white-text " width="20%">G</th>
              <th class="letter valign red darken-1 white-text " width="20%">O</th>
            </tr>
            <tr><td class="ball colorcall called" id="00" style="font-size: 30px; background-color: rgb(30, 187, 0);">15</td><td class="ball colorcall called" id="01" style="font-size: 30px; background-color: rgb(30, 187, 0);">22</td><td class="ball" id="02" style="font-size: 30px; background-color: rgb(255, 255, 255);">37</td><td class="ball" id="03" style="font-size: 30px; background-color: rgb(255, 255, 255);">56</td><td class="ball" id="04" style="font-size: 30px; background-color: rgb(255, 255, 255);">65</td></tr><tr><td class="ball colorcall called brad" id="10" style="font-size: 30px; background-color: rgb(252, 126, 3);">2</td><td class="ball colorcall called" id="11" style="font-size: 30px; background-color: rgb(30, 187, 0);">27</td><td class="ball" id="12" style="font-size: 30px; background-color: rgb(255, 255, 255);">41</td><td class="ball colorcall called" id="13" style="font-size: 30px; background-color: rgb(30, 187, 0);">52</td><td class="ball" id="14" style="font-size: 30px; background-color: rgb(255, 255, 255);">68</td></tr><tr><td class="ball" id="20" style="font-size: 30px; background-color: rgb(255, 255, 255);">7</td><td class="ball colorcall called" id="21" style="font-size: 30px; background-color: rgb(30, 187, 0);">17</td><td id="22" disabled="" style="cursor: not-allowed; background-color: rgb(30, 187, 0);" class="colorcall called">Free</td><td class="ball" id="23" style="font-size: 30px; background-color: rgb(255, 255, 255);">57</td><td class="ball colorcall called brad" id="24" style="font-size: 30px; background-color: rgb(252, 126, 3);">67</td></tr><tr><td class="ball colorcall called brad" id="30" style="font-size: 30px; background-color: rgb(252, 126, 3);">14</td><td class="ball colorcall called" id="31" style="font-size: 30px; background-color: rgb(30, 187, 0);">18</td><td class="ball" id="32" style="font-size: 30px; background-color: rgb(255, 255, 255);">36</td><td class="ball colorcall called" id="33" style="font-size: 30px; background-color: rgb(30, 187, 0);">59</td><td class="ball" id="34" style="font-size: 30px; background-color: rgb(255, 255, 255);">73</td></tr><tr><td class="ball colorcall called brad" id="40" style="font-size: 30px; background-color: rgb(252, 126, 3);">11</td><td class="ball colorcall called" id="41" style="font-size: 30px; background-color: rgb(30, 187, 0);">24</td><td class="ball" id="42" style="font-size: 30px; background-color: rgb(255, 255, 255);">38</td><td class="ball colorcall called brad" id="43" style="font-size: 30px; background-color: rgb(252, 126, 3);">58</td><td class="ball colorcall called" id="44" style="font-size: 30px; background-color: rgb(30, 187, 0);">63</td></tr>          </tbody></table>  
            <input type="text" name="0" id="0" value="O,O,O,O,O" hidden="" style="width: 20px; height: 20px;"><input type="text" name="1" id="1" value="O,O,O,O,O" hidden="" style="width: 20px; height: 20px;"><input type="text" name="2" id="2" value="O,O,O,O,O" hidden="" style="width: 20px; height: 20px;"><input type="text" name="3" id="3" value="O,O,O,O,O" hidden="" style="width: 20px; height: 20px;"><input type="text" name="4" id="4" value="O,O,O,O,O" hidden="" style="width: 20px; height: 20px;">        
        <input type="text" name="card" id="card" onkeydown="handleEnterKey(event)" placeholder="Cartela No.">        
            <button type="submit" name="add_card" id="check" class="btn btn-primary chk">Lock</button>
        <span id="card_status" style="color: rgb(30, 187, 0);">አሸንፏል</span>

        </div>
<script type="text/javascript">
    "use strict";
/**
 * Main Bingo Class
 * @param bingoBoardElement
 * @param speechInstance
 * @constructor
 */
//alert(sessionStorage.getItem("voice"));
var voice = sessionStorage.getItem("voice");
const soundUrls = [];
        for (let i = 1; i <= 75; i++) {
            soundUrls.push((`audio/`).concat(voice).concat(i).concat(`.mp3`)); // Replace with your sound file URLs
        }
        soundUrls.push(`audio/buzz.mp3`);
        soundUrls.push(`audio/fireworks.mp3`);
        soundUrls.push(`audio/play.mp3`);
        soundUrls.push(`audio/puase.mp3`);
        soundUrls.push(`audio/shuffle.mp3`);

        let db;

        // Open or create IndexedDB database
        const dbRequest = indexedDB.open('SoundDB', 1);

        dbRequest.onupgradeneeded = (event) => {
            db = event.target.result;
            if (!db.objectStoreNames.contains('sounds')) {
                db.createObjectStore('sounds', { keyPath: 'id' });
                console.log('Object store "sounds" created.');
            }
        };



        dbRequest.onsuccess = async (event) => {
            db = event.target.result;
            console.log('Database opened successfully.');

            // Check and update sounds if necessary
            await checkAndUpdateSounds();
        };

        dbRequest.onerror = (event) => {
            console.error('Error opening database:', event.target.errorCode);
        };

        async function checkAndUpdateSounds() {
            try {
                const soundCount = await getStoredSoundCount();
                displayStoredSoundCount(soundCount);
                if(soundCount<80){
                    await clearAllSounds();
                    await storeAllSounds();
                }
            document.getElementById('shuffle').classList.remove('disabled');
            document.getElementById('resumeGame').classList.remove('disabled');
            } catch (error) {
                console.error('Error during check and update:', error);
            }
        }

        // Function to count stored sounds
        function getStoredSoundCount() {
            return new Promise((resolve, reject) => {
                const transaction = db.transaction(['sounds'], 'readonly');
                const objectStore = transaction.objectStore('sounds');
                let count = 0;

                const request = objectStore.openCursor();
                request.onsuccess = (event) => {
                    const cursor = event.target.result;
                    if (cursor) {
                        count++;
                        cursor.continue();
                    } else {
                        resolve(count);
                    }
                };

                request.onerror = (event) => {
                    reject('Error counting sounds:', event.target.errorCode);
                };
            });
        }

        // Function to clear all sounds from the database
        function clearAllSounds() {
            return new Promise((resolve, reject) => {
                const transaction = db.transaction(['sounds'], 'readwrite');
                const objectStore = transaction.objectStore('sounds');

                const request = objectStore.clear();
                request.onsuccess = () => {
                    console.log('All sounds cleared successfully.');
                    resolve();
                };

                request.onerror = (event) => {
                    console.error('Error clearing sounds:', event.target.errorCode);
                    reject('Failed to clear sounds.');
                };
            });
        }

        // Function to store all sounds with progress display
        async function storeAllSounds() {
            const loadingMessage = document.getElementById('loadingMessage');
            const progressCount = document.getElementById('progressCount');

            loadingMessage.style.display = 'block';
            loadingMessage.style.color = 'white';
            let count = 0;

            for (const soundUrl of soundUrls) {
                const isStored = await checkIfSoundExists(soundUrl);
                if (!isStored) {
                    await storeSoundByUrl(soundUrl);
                    count++;
                    progressCount.textContent = count;
                }
            }

            loadingMessage.textContent = `Stored ${count} new sounds successfully.`;
            //loadingMessage.style.display = 'none';
            displayStoredSoundCount(await getStoredSoundCount());
        }

        // Function to check if a sound file already exists in IndexedDB
        function checkIfSoundExists(soundUrl) {
            return new Promise((resolve) => {
                const fileName = soundUrl.split('/').pop();
                const transaction = db.transaction(['sounds'], 'readonly');
                const objectStore = transaction.objectStore('sounds');
                const getRequest = objectStore.get(fileName);

                getRequest.onsuccess = (event) => {
                    resolve(event.target.result !== undefined);
                };

                getRequest.onerror = (event) => {
                    console.error('Error checking file:', event.target.errorCode);
                    resolve(false);
                };
            });
        }

        // Function to fetch and store a sound file by URL in IndexedDB
        async function storeSoundByUrl(soundUrl) {
            try {
                const response = await fetch(soundUrl);
                if (!response.ok) {
                    throw new Error('Failed to fetch sound file');
                }
                const blob = await response.blob();
                const fileName = soundUrl.split('/').pop();

                const transaction = db.transaction(['sounds'], 'readwrite');
                const objectStore = transaction.objectStore('sounds');

                const fileRecord = {
                    id: fileName,
                    file: blob
                };

                const addRequest = objectStore.add(fileRecord);
                addRequest.onsuccess = () => {
                    console.log(`Sound file ${fileName} stored successfully`);
                };

                addRequest.onerror = (event) => {
                    console.error('Error storing file:', event.target.errorCode);
                };
            } catch (error) {
                console.error('Error fetching sound file:', error);
                //alert('Failed to fetch and store the sound file.');
            }
        }
        // Function to display stored sound count
        function displayStoredSoundCount(count) {
            //document.getElementById('soundCount').textContent = count;
        }
        
        // Function to reset the IndexedDB database and local storage
        async function resetDatabaseAndStorage() {
            // Clear local storage
            localStorage.clear();
            console.log('Local storage cleared.');

            // Delete the IndexedDB database
            try {
                await new Promise((resolve, reject) => {
                    const deleteRequest = indexedDB.deleteDatabase('SoundDB');

                    deleteRequest.onsuccess = () => {
                        console.log('IndexedDB database deleted successfully.');
                        resolve();
                    };

                    deleteRequest.onerror = (event) => {
                        console.error('Error deleting database:', event.target.errorCode);
                        reject('Failed to delete IndexedDB database.');
                    };
                });

                // Reinitialize the database after reset
                await initializeDatabase();
            } catch (error) {
                console.error('Error during reset:', error);
            }
        }
        function playsound(patz){
                        const soundName = patz;

            if (soundName) {
                const transaction = db.transaction(['sounds'], 'readonly');
                const objectStore = transaction.objectStore('sounds');
                const getRequest = objectStore.get(soundName);

                getRequest.onsuccess = (event) => {
                    const fileRecord = event.target.result;

                    if (fileRecord) {
                        const audio = new Audio(URL.createObjectURL(fileRecord.file));
                        audio.play();
                    } else {
                        checkAndUpdateSounds();
                    }
                };

                getRequest.onerror = (event) => {
                    console.error('Error retrieving sound:', event.target.errorCode);
                };
            } else {
                alert('Please enter a sound name.');
            }
        }
        function checksound(patz){
                        const soundName = patz;
                const transaction = db.transaction(['sounds'], 'readonly');
                const objectStore = transaction.objectStore('sounds');
                const getRequest = objectStore.get(soundName);

                getRequest.onsuccess = (event) => {
                    const fileRecord = event.target.result;

                    if (fileRecord) {
                        return 1;
                    } else {
                        return 0;
                    }
                };

                getRequest.onerror = (event) => {
                    return 0;
                };
            }

var Bingo = function(bingoBoardElement, speechInstance) {
    /**
     * Array of the Bingo letters
     * @type {[*]}
     */
    var bingoLetters = ["B", "I", "N", "G", "O"];

    /**
     * Array to hold all of the potential bingo numbers
     * @type {Array}
     */
    var allBingoNumbers = [];

    /**
     * Array to hold called bingo numbers
     * @type {Array}
     */
    this.calledBingoNumbers = [];

    /**
     * Interval for calling balls
     * @type {number}
     */
    var ballCallingInterval = window.setInterval(null, parseInt(document.getElementById('range').value) * 1000);

    /**
     * States whether a game has started or not
     * @type {boolean}
     */
    var hasGameStarted = false;

    /**
     * Run the bingo process
     */
    this.run = function() {
        /**
         * Initialize the bingo board
         */
        generateBingoBoard();
        /**
         * Add event listeners to buttons
         */
        addEventListeners();
        /**
         * Initialize speech synthesis
         */
        speechInstance.initSpeechSynthesis();
    };

    /**
     * Generate the bingo board
     */
    function generateBingoBoard() {
        /**
         * Variable that holds the current bingo ball number
         * @type {number}
         */
        var currentBingoBall = 1;
        // Loop through the bingo letters, creating dom elements as needed - then append elements and bingo numbers
        for(var i = 0; i < bingoLetters.length; i++){
            var letterBlock = helper.createDomElement('div', 'letter-block valign-wrapper ');
            var bingoLetter = helper.createDomElement('div', 'letter valign red darken-1 white-text ', bingoLetters[i]);
            letterBlock.appendChild(bingoLetter);
            bingoBoardElement.appendChild(letterBlock);
            // get back the current ball we left off at for generating the next block
            currentBingoBall = add15Balls(allBingoNumbers, currentBingoBall, letterBlock, bingoLetters[i]);
        }
    }
    /**
     * Generate numbers for populating the bingo board
     * @param allBingoNumbers
     * @param currentBingoBall
     * @param letterBlock
     * @param letter
     * @returns {*}
     */
    function add15Balls(allBingoNumbers, currentBingoBall, letterBlock, letter) {
        var totalBingoBalls = currentBingoBall + 15;
        for (currentBingoBall; currentBingoBall < totalBingoBalls; currentBingoBall++) {
            var newBingoBall = helper.createDomElement('div', 'ball valign opacit ' + letter + currentBingoBall);
            newBingoBall.appendChild(document.createTextNode(currentBingoBall));
            newBingoBall.setAttribute('id', letter + currentBingoBall);
            letterBlock.appendChild(newBingoBall);
            allBingoNumbers.push(letter + currentBingoBall);
        }
        return currentBingoBall;
    }

    /**
     * Add event listeners to buttons
     */
    function addEventListeners () {
        window.addEventListener('load', function() {
        $.ajax({
        type: 'POST',
        url: 'sale.php',
        data: {
            user: sessionStorage.getItem('user_info'),
            game: sessionStorage.getItem('game_info')
        },
        success: function(response) {
        }                
    });
            //const bingoNumber = "shuffle.mp3";
            //playsound(bingoNumber);
        });
        document.getElementById('show').addEventListener('click', pauseGameListener);
        document.getElementById('resetGame').addEventListener('click', resetGameListener);
        document.getElementById('pauseGame').addEventListener('click', pauseGameListener);
        document.getElementById('resumeGame').addEventListener('click', resumeGameListener);
    }

    /**
     * Pause Game Listener
     */
    function pauseGameListener(){
        if(this.id=="show")
            if(sessionStorage.getItem("called").split(",").length<5){
            event.preventDefault();
        return false;
        }
        if(String(document.getElementById('resumeGame').classList).search('disabled')>0)
            playsound("puase.mp3");
        clearInterval(ballCallingInterval);
        document.getElementById('resumeGame').classList.remove('disabled');
        document.getElementById('show').classList.remove('disabled');
        document.getElementById('pauseGame').classList.add('disabled');
    }

    /**
     * Resume Game Listener
     */
    function resumeGameListener(){
        if(document.getElementById('resumeGame').innerHTML=="Play"){
        document.getElementById('pauseGame').classList.remove('disabled');
        document.getElementById('show').classList.remove('disabled');
        this.classList.add('disabled');
        // if currently set to manual, change to the next value then continue calling
        if(parseInt(document.getElementById('range').value) === 16) {
            document.getElementById('range').value = 15;
        }
        callBingoBall();
        clearInterval(ballCallingInterval);
        ballCallingInterval = window.setInterval(callBingoBall, parseInt(document.getElementById('range').value) * 1000);
    }
    else{
        document.getElementById('resumeGame').innerHTML="Play";
        hasGameStarted = true;
        //speechInstance.say("Let's play bingo!");
        this.classList.add('disabled');
        document.getElementById('shuffle').classList.add('disabled');
        document.getElementById('show').classList.remove('disabled');
        document.getElementById('pauseGame').classList.remove('disabled');
        document.getElementById('resetGame').classList.remove('disabled');
        document.getElementById('show').classList.remove('disabled');

        callBingoBall();
        ballCallingInterval = window.setInterval(callBingoBall, (parseInt(document.getElementById('range').value) * 1000));
    }
    }

    /**
     * Reset Game Listener
     */
    function resetGameListener(){        
        $.ajax({
            type: 'POST',
            url: 'sale.php',
            data: {
                reset: sessionStorage.getItem('game_info'),
                lastcall: sessionStorage.getItem("called").split(",").length-1
            },
            success: function(response) {
            }                
        });
        //if (confirm("Do You Want To Reset Bingo Caller?") == true){
        clearInterval(ballCallingInterval);
        // reset the called bingo numbers
        bingoInstance.calledBingoNumbers = [];
        // reset the array of all bingo numbers
        allBingoNumbers = [];
        // clear bingo board
        bingoBoardElement.innerHTML = '';
        // reset inner HTML for ball
        document.getElementById('callNumber').innerHTML = "";
        sessionStorage.setItem("called", "");
        // clear the current ball
        document.getElementById('ballText').innerHTML = '';
        document.getElementById('ballGraphic').className = '';
        document.getElementById('pauseGame').classList.add('disabled');
        document.getElementById('resumeGame').classList.add('disabled');
        document.getElementById('check').classList.add('disabled');
        this.classList.add('disabled');
        window.location.href = "product.php";
                
    }
    
    function changeDelayListener() {
        if(hasGameStarted) {
            var delayValue = parseInt(document.getElementById('range').value);
            if (delayValue === 16) {
                document.getElementById('check').classList.remove('disabled');
                document.getElementById('resumeGame').classList.remove('disabled');
                document.getElementById('pauseGame').classList.add('disabled');
                clearInterval(ballCallingInterval);
            } else {
                document.getElementById('check').classList.add('disabled');
                clearInterval(ballCallingInterval);
                ballCallingInterval = window.setInterval(callBingoBall, parseInt(document.getElementById('range').value) * 1000);
            }
        } else {
            console.log("game hasn't started");
        }
    }

    /**
     * Function for calling bingo balls
     */
    function callBingoBall() {
        // if we have already called all possible numbers, quit.
        if(bingoInstance.calledBingoNumbers.length === 75){
            window.clearInterval(ballCallingInterval);
        } else {
            if ('speechSynthesis' in window) {
                // cancel any current speech
                window.speechSynthesis.cancel();
            }

            // set elements for displaying the current ball as variables
            var lastBallCalled = document.getElementById('ballText').innerHTML.replace('<br>',''),
                ballGraphicElement = document.getElementById('ballGraphic'),
                ballTextElement = document.getElementById('ballText'),
                // generate a new ball number
                newBallNumber = allBingoNumbers[Math.floor(Math.random() * allBingoNumbers.length)],
                // split the numbers for reading aloud
                split = newBallNumber.split(""),
                // generate ball text for appending to ball text element
                ballText = split[0] + "<br>" + split[1] + (split[2] ? split[2] : '');

                var text1 = newBallNumber[0];
                var text2 = newBallNumber[1];
                for (var i = 2; i < newBallNumber.length; i++) {
                    text2 = text2.concat(newBallNumber[i]);
                }
                //alert(text2);
            // if speech is enabled, call the numbers aloud
            speechInstance.say(newBallNumber);
            /*for (var a = 0; a < split.length; a++) {
                speechInstance.say(split[a].toLowerCase());
            }*/

            // using the letter from the bingo call, determine the color of the ball
            var color = '';
            switch (split[0]) {
                case 'B':
                    color = 'blue';
                    break;
                case 'I':
                    color = 'red';
                    break;
                case 'N':
                    color = 'white';
                    break;
                case 'G':
                    color = 'green';
                    break;
                case 'O':
                    color = 'orange';
                    break;
            }

            // set classes for the ball graphic elements
            ballGraphicElement.className = "valign-wrapper " + color;
            ballTextElement.className = "valign center-align " + (split[2] ? newBallNumber : 'single ' + newBallNumber);
            // Change ball text for the ball text element
            ballTextElement.innerHTML = ballText;

            // if there's a number called on the board, grab that element so we can change classes
            var lastCallOnBoard = document.getElementsByClassName('lastCall');
            if(lastCallOnBoard.length > 0){
                lastCallOnBoard[0].classList.add('called');
                lastCallOnBoard[0].classList.add('border');
                lastCallOnBoard[0].classList.add('brad');
                lastCallOnBoard[0].classList.remove('lastCall');
            }
            else{
                sessionStorage.setItem("called", "");
                document.getElementById(newBallNumber).classList.add('lastCall');
            }
            // if not the first ball called, add last call to the most recent called ball
            if(lastBallCalled){
                document.getElementById(newBallNumber).classList.add('lastCall');
            }

            // get the index of the new ball in all bingo numbers
            var index = allBingoNumbers.indexOf(newBallNumber);
            // remove the called number from the list of bingo numbers
            allBingoNumbers.splice(index,1);
            // add the called number to the list of called bingo numbers
            bingoInstance.calledBingoNumbers.push(newBallNumber);

            // keep track of number of balls called.
            document.getElementById('callNumber').innerHTML = bingoInstance.calledBingoNumbers.length.toString() + " / 75</span>";
            sessionStorage.setItem("called", sessionStorage.getItem("called").concat(newBallNumber.concat(",")));
            let personName = sessionStorage.getItem("called");
        }
    }
};

/**
 * Speech class for all voice synthesis functionality
 * @constructor
 */
var Speech = function() {
    /**
     * Voice object, populated by user input
     * @type {object}
     */
    this.voice = null;
    /**
     * Bool to hold whether speech is enabled or not
     * Defaults to false until the speech engine loads
     * @type {boolean}
     */
    var speechEnabled = false;


    /**
     * Public function for speaking text aloud
     * @param text
     */
    this.say = function (text) {
        if(true) {
            let patz="";
            patz=patz.concat(voice);
                for(var i=1;i<text.length;i++)
                    patz = patz.concat(text[i]);
                patz = patz.concat(".mp3");
            
            playsound(patz);

        }
    };
};

/**
 * Helper object for any static helper methods
 * @constructor
 */
var helper = {
    /**
     * Private function that will generate a new dom element
     * @param type
     * @param classes
     * @param content
     * @returns {Element}
     */
    createDomElement: function(type, classes, content){
        var element = document.createElement(type);
        element.className = typeof classes !== 'undefined' || classes == '' ? classes : '';
        element.innerHTML = typeof content !== 'undefined' || content == '' ? content : '';
        return element;
    }
};

/**
 * Define the bingo board element
 * @type {Element}
 */
var bingoBoardElement = document.getElementById('bingoboard');

/**
 * Create new instance of the speech class
 * @type {Speech}
 */
var speechInstance = new Speech();

/**
 * Create new instance of the Bingo Board class
 * @type {Bingo}
 */
var bingoInstance = new Bingo(bingoBoardElement, speechInstance);

bingoInstance.run();</script>
<script src="js/confetti.browser.min.js"></script>
<script type="text/javascript">
function celebrate() {
    confetti({
        particleCount: 1000,
        spread: 200,
        origin: { y: 0.8 }
    });
}

</script>

<style>
        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 90%;
            max-width: 1200px;
            position: relative;
        }
        .bounce-container {
            position: absolute;
            top: 20px;
            left: 450px;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.2); /* Semi-transparent white background */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            border-radius: 50%; /* Rounded corners */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            margin-bottom: 10px;
            transition: opacity 0.5s ease; /* Smooth opacity transition */
            overflow: hidden;
            z-index: 1000;
            display: none;
        }
        .bounce-ball {
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f0f0f0;
            font-weight: bold;
            font-size: 18px;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        .dot {
            width: 10px; /* Smaller size for dot */
            height: 10px; /* Smaller size for dot */
            border-radius: 50%; /* Make it a circle */
            background: #f0f0f0; /* Same color as the ball */
            opacity: 1; /* Fully visible */
            transition: opacity 0.5s ease, transform 0.5s ease; /* Transition for dots */
        }
    </style>


    <script>
        const bounceContainer = document.getElementById('bounceContainer');
        const totalBalls = 75; // Number of balls
        let balls = []; // Array to hold ball elements

        function createBalls() {
            for (let i = 1; i <= totalBalls; i++) {
                const ball = document.createElement('div');
                ball.classList.add('bounce-ball');
                ball.textContent = i;
                ball.id = `ball${i}`;
                setRandomPosition(ball);
                bounceContainer.appendChild(ball);
                balls.push(ball); // Store the ball in the array
            }
        }

        function setRandomPosition(ball) {
            const containerWidth = bounceContainer.offsetWidth;
            const containerHeight = bounceContainer.offsetHeight;
            ball.style.left = `${Math.random() * (containerWidth - 80)}px`; // Random x position
            ball.style.top = `${Math.random() * (containerHeight - 80)}px`; // Random y position
        }

        function continuouslyShuffleBalls() {
            balls.forEach(ball => {
                setRandomPosition(ball); // Update position of each ball
            });
            requestAnimationFrame(continuouslyShuffleBalls); // Call the function continuously
        }

        function transformContainer() {
            // Transform the bounce container into a dot
            bounceContainer.style.transition = 'transform 1s ease, opacity 1s ease';
            bounceContainer.style.transform = 'scale(30)'; // Scale down to 0
            bounceContainer.style.opacity = '0'; // Fade out

            // Remove the container after the animation
            setTimeout(() => {
                bounceContainer.remove(); // Remove container
            }, 1000); // Match this to the transition duration
        }

        function transformAndDisappear() {
            balls.forEach(ball => { 
                setTimeout(() => {
                    ball.remove(); // Remove original ball
                    dot.remove(); // Remove dot after fade out
                }, 1000);
            });
        }

        function shuffle() {
            document.getElementById('bounceContainer').style.display="block";
            playsound("shuffle.mp3");
            setTimeout(() => {
            createBalls();
            continuouslyShuffleBalls();
            }, 500);
            setTimeout(() => {
                transformAndDisappear();
                transformContainer();
            }, 3000);
            document.getElementById('shuffle').classList.add('disabled');
        }
    </script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-586d4324ccbaf284"></script>
<script src="js/script.js"></script>
<script type="text/javascript">
    
        const card_list = sessionStorage.getItem("card_list_402");
        const no_cards = card_list.split("*");
        var cards = "";
        for(var i=0;i<no_cards.length-1;i++){
            cards = cards.concat(no_cards[i].split("/")[0]).concat(",");

        }
        document.getElementById('cards').value=cards;
        if(sessionStorage.getItem('setting_402').split(",")[1]>=1 && sessionStorage.getItem('setting_402').split(",")[1]<=10)
            document.getElementById('range').value=sessionStorage.getItem('setting_402').split(",")[1];
        document.getElementById('prize').innerHTML=("BIRR:- ").concat(String((sessionStorage.getItem('game_info').split("-")[0]*sessionStorage.getItem('game_info').split("-")[1])-(sessionStorage.getItem('game_info').split("-")[0]*sessionStorage.getItem('game_info').split("-")[1]*sessionStorage.getItem('game_info').split("-")[2]).toFixed(0))).concat(".00");
        document.getElementById('prizes').innerHTML=("").concat(String((sessionStorage.getItem('game_info').split("-")[0]*sessionStorage.getItem('game_info').split("-")[1])-(sessionStorage.getItem('game_info').split("-")[0]*sessionStorage.getItem('game_info').split("-")[1]*sessionStorage.getItem('game_info').split("-")[2]).toFixed(0))).concat(".00");
        document.getElementById('price').innerHTML=("BET:- ").concat(String(sessionStorage.getItem('game_info').split("-")[1])).concat(".00");
</script>

<div class="hiddendiv common"></div></body></html>