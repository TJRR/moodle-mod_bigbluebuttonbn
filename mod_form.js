/**
 * @package   mod_bigbluebuttonbn
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2014-2015 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

bigbluebuttonbn_participant_selection_set = function() {
    bigbluebuttonbn_select_clear('bigbluebuttonbn_participant_selection');

    var type = document.getElementById('bigbluebuttonbn_participant_selection_type');
    for( var i = 0; i < type.options.length; i++ ){
        if( type.options[i].selected ) {
            var options = bigbluebuttonbn_participant_selection[type.options[i].value];
            for( var j = 0; j < options.length; j++ ) {
                bigbluebuttonbn_select_add_option('bigbluebuttonbn_participant_selection', options[j].name, options[j].id);
            }
            if( j == 0){
                bigbluebuttonbn_select_add_option('bigbluebuttonbn_participant_selection', '---------------', 'all');
                bigbluebuttonbn_select_disable('bigbluebuttonbn_participant_selection')
            } else {
                bigbluebuttonbn_select_enable('bigbluebuttonbn_participant_selection')
            }
        }
    }
}

bigbluebuttonbn_participant_list_update = function() {
    var participant_list = document.getElementsByName('participants')[0];
    participant_list.value = JSON.stringify(bigbluebuttonbn_participant_list).replace(/"/g, '&quot;');
}

bigbluebuttonbn_participant_remove = function(type, id) {
    //Remove from memory
    for( var i = 0; i < bigbluebuttonbn_participant_list.length; i++ ){
        if( bigbluebuttonbn_participant_list[i].selectiontype == type && bigbluebuttonbn_participant_list[i].selectionid == (id == ''? null: id) ){
            bigbluebuttonbn_participant_list.splice(i, 1);
        }
    }

    //Remove from the form
    var participant_list_table = document.getElementById('participant_list_table');
    for( var i = 0; i < participant_list_table.rows.length; i++ ){
        if( participant_list_table.rows[i].id == 'participant_list_tr_' + type + '-' + id  ) {
            participant_list_table.deleteRow(i);
        }
    }
    bigbluebuttonbn_participant_list_update();
}

bigbluebuttonbn_participant_add = function() {
    var participant_selection_type = document.getElementById('bigbluebuttonbn_participant_selection_type');
    var participant_selection = document.getElementById('bigbluebuttonbn_participant_selection');

    //Lookup to see if it has been added already
    var found = false;
    for( var i = 0; i < bigbluebuttonbn_participant_list.length; i++ ){
        if( bigbluebuttonbn_participant_list[i].selectiontype == participant_selection_type.value && bigbluebuttonbn_participant_list[i].selectionid == participant_selection.value ){
            found = true;
        }
    }

    //If not found
    if( !found ){
        // Add it to memory
        var participant = {"selectiontype": participant_selection_type.value, "selectionid": participant_selection.value, "role": "viewer"};
        bigbluebuttonbn_participant_list.push(participant);

        // Add it to the form
        var participant_list_table = document.getElementById('participant_list_table');
        var row = participant_list_table.insertRow(participant_list_table.rows.length);
        row.id = "participant_list_tr_" + participant_selection_type.value + "-" + participant_selection.value;
        var cell0 = row.insertCell(0);
        cell0.width = "20px";
        cell0.innerHTML = '<a onclick="bigbluebuttonbn_participant_remove(\'' + participant_selection_type.value + '\', \'' + participant_selection.value + '\'); return 0;" title="' + bigbluebuttonbn_strings.remove + '">x</a>';
        var cell1 = row.insertCell(1);
        cell1.width = "125px";
        if( participant_selection_type.value == 'all' )
            cell1.innerHTML = '<b><i>' + participant_selection_type.options[participant_selection_type.selectedIndex].text + '</i></b>';
        else
            cell1.innerHTML = '<b><i>' + participant_selection_type.options[participant_selection_type.selectedIndex].text + ':&nbsp;</i></b>';
        var cell2 = row.insertCell(2);
        if( participant_selection_type.value == 'all' )
            cell2.innerHTML = '';
        else
            cell2.innerHTML = participant_selection.options[participant_selection.selectedIndex].text;
        var cell3 = row.insertCell(3);
        cell3.innerHTML = '<i>&nbsp;' + bigbluebuttonbn_strings.as + '&nbsp;</i><select id="participant_list_role_' + participant_selection_type.value + '-' + participant_selection.value + '" onchange="bigbluebuttonbn_participant_list_role_update(\'' + participant_selection_type.value + '\', \'' + participant_selection.value + '\'); return 0;"><option value="viewer" selected="selected">' + bigbluebuttonbn_strings.viewer + '</option><option value="moderator">' + bigbluebuttonbn_strings.moderator + '</option></select>';
    }

    bigbluebuttonbn_participant_list_update();
}

bigbluebuttonbn_participant_list_role_update = function(type, id) {
    // Update in memory
    var participant_list_role_selection = document.getElementById('participant_list_role_' + type + '-' + id);
    for( var i = 0; i < bigbluebuttonbn_participant_list.length; i++ ){
        if( bigbluebuttonbn_participant_list[i].selectiontype == type && bigbluebuttonbn_participant_list[i].selectionid == (id == ''? null: id) ){
            bigbluebuttonbn_participant_list[i].role = participant_list_role_selection.value;
            //participant_list_role_selection.options[participant_list_role_selection.selectedIndex].text
        }
    }

    // Update in the form
    bigbluebuttonbn_participant_list_update();
}

bigbluebuttonbn_select_clear = function(id) {
    var select = document.getElementById(id);
    while( select.length > 0 ){
        select.remove(select.length-1);
    }
}

bigbluebuttonbn_select_enable = function(id) {
    var select = document.getElementById(id);
    select.disabled = false;
}

bigbluebuttonbn_select_disable = function(id) {
    var select = document.getElementById(id);
    select.disabled = true;
}

bigbluebuttonbn_select_add_option = function(id, text, value) {
    var select = document.getElementById(id);
    var option = document.createElement('option');
    option.text = text;
    option.value = value;
    select.add(option , 0);
}

httpGet = function(theUrl)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange=function() {
    if (xmlHttp.readyState === 4){   //if complete
            if(xmlHttp.status === 200){  //check if "OK" (200)
                //success
            } else {
                //return 'erro';
            }
        }
    }
    xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

verificaSala = function(){
  document.getElementsByClassName('visibleifjs')[0].style.visibility = "hidden";
  document.getElementsByClassName('visibleifjs')[1].style.visibility = "hidden";
  if(document.getElementById('id_openingtime_enabled').checked == false || document.getElementById('id_closingtime_enabled').checked == false){
    document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
    document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");
    // var selecteds = 0;
    // var selecao_valores =  document.getElementById('id_select_rooms').options;
    // for (var i = 0; i < selecao_valores.length; i++) {
    //   if(selecao_valores[i].selected){
    //     selecteds=1;
    //     break;
    //   }
    // }
    // if(selecteds==1){
    //   document.getElementById('id_submitbutton').removeAttribute("disabled");
    //   document.getElementById('id_submitbutton2').removeAttribute("disabled");
    // }else{
    //   document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
    //   document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");
    // }
  }else{
    var openingtime = 0;
    var data = document.getElementById('id_openingtime_year').value + '-' + ("0" + document.getElementById('id_openingtime_month').value).substr(-2) + '-' + ("0"+document.getElementById('id_openingtime_day').value).substr(-2) + 'T' + ("0"+document.getElementById('id_openingtime_hour').value).substr(-2) + ':'+ ("0"+document.getElementById('id_openingtime_minute').value).substr(-2) +':00Z';
    openingtime = new Date(data);
    //openingtime = new Date( openingtime.getUTCFullYear(), openingtime.getUTCMonth(), openingtime.getUTCDate(), openingtime.getUTCHours(), openingtime.getUTCMinutes(), openingtime.getUTCSeconds());
    var time_ini = openingtime.getTime()/1000;

    var data_fim = document.getElementById('id_closingtime_year').value + '-' + ("0" + document.getElementById('id_closingtime_month').value).substr(-2) + '-' + ("0"+document.getElementById('id_closingtime_day').value).substr(-2) + 'T' + ("0"+document.getElementById('id_closingtime_hour').value).substr(-2) + ':'+ ("0"+document.getElementById('id_closingtime_minute').value).substr(-2) +':00Z';
    endingtime = new Date(data_fim);
    var time_fim = endingtime.getTime()/1000;
    var selecao_valores =  document.getElementById('id_select_rooms').options;
    var valido = 1;
    var selecteds = 0;
    var id_bbb = '';
    if(document.getElementsByName('idbbb_update')[0].value != '0'){
      id_bbb = '&id_bbb='+document.getElementsByName('idbbb_update')[0].value;
    }
    for (var i = 0; i < selecao_valores.length; i++) {
      if(selecao_valores[i].selected){
        var retornohttp = httpGet(document.getElementById('base_url_get').value+'get_salas.php?id='+selecao_valores[i].value+'&date_ini='+time_ini+'&date_fim='+time_fim+id_bbb);
        if(retornohttp==1){
          if(valido==1){
            alert("Já existe uma audiência marcada nesta sala para esta data. Por favor escolha outra sala ou outra data.");
          }
          document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
          document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");
          valido = 0;
          selecao_valores[i].selected = false;
        }else{
          if(retornohttp==0){
            selecteds = 1;
          }else{
            if(valido==1){
              alert('Não foi possível acessar o servidor para reservar a sala! Tente novamente');
            }
            document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
            document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");
            valido = 0;
            selecao_valores[i].selected = false;
          }
        }
      }
    }
    if(valido == 1 && selecteds == 1){
      document.getElementById('id_submitbutton').removeAttribute("disabled");
      document.getElementById('id_submitbutton2').removeAttribute("disabled");
    }else{
      document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
      document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");
    }
  }
}

verificaProcesso = function(){
  document.getElementsByName('name')[0].value = document.getElementById('id_nr_process').value;
  document.getElementsByClassName('visibleifjs')[0].style.visibility = "hidden";
  document.getElementsByClassName('visibleifjs')[1].style.visibility = "hidden";
  var existe_processo = httpGet(document.getElementById('base_url_get').value+'get_process_saved.php?nrprocesso='+document.getElementById('id_nr_process').value);
  if(existe_processo==1){
    alert("Esse processo já foi cadastrado no sistema de videoconferência, utilize o sistema de busca para localizá-lo");
    return 1;
  }else{
    return 0;
  }
}

gravaPartes = function(element, index, array){
  var nome = element.getElementsByTagName('nome')[0].innerHTML;
  var guardado = document.getElementsByName('partes')[0].value;
  if (guardado != '') {
    guardado = guardado + '///';
  }
  document.getElementsByName('partes')[0].value = guardado + nome;
  document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = document.getElementById('id_introeditoreditable').childNodes[0].innerHTML+"<br>Parte: "+nome;
  document.getElementById('id_welcome').value = document.getElementById('id_welcome').value+"\nParte: "+nome;
  var adv = element.getElementsByTagName('advogados')[0];
  var guardadoAdv = document.getElementsByName('advogados')[0].value;
  if (guardadoAdv != '') {
    guardadoAdv = guardadoAdv + '///';
  }
  if(adv.innerHTML == ''){
    document.getElementsByName('advogados')[0].value = guardadoAdv + ' ';
    document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = document.getElementById('id_introeditoreditable').childNodes[0].innerHTML+" - sem advogado";
    document.getElementById('id_welcome').value = document.getElementById('id_welcome').value+" - sem advogado";
  }else{
    advEach = adv.getElementsByTagName('advogado')[0].childNodes;
    var nomeAdv = advEach[3].innerHTML + '---' + advEach[0].innerHTML + '/' + advEach[2].innerHTML;
    document.getElementsByName('advogados')[0].value = guardadoAdv + nomeAdv;
    document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = document.getElementById('id_introeditoreditable').childNodes[0].innerHTML+" - Adv: "+advEach[3].innerHTML+" - OAB: "+advEach[0].innerHTML+"/"+advEach[2].innerHTML;
    document.getElementById('id_welcome').value = document.getElementById('id_welcome').value+" - Adv: "+advEach[3].innerHTML+" - OAB: "+advEach[0].innerHTML+"/"+advEach[2].innerHTML;
  }
}

bigbluebuttonbn_process_get = function() {

    var tipo_proc = document.getElementById('id_process_type').value;

    document.getElementsByClassName('visibleifjs')[0].style.visibility = "hidden";
    document.getElementsByClassName('visibleifjs')[1].style.visibility = "hidden";

    document.getElementById('id_submitbutton').setAttribute("disabled","disabled");
    document.getElementById('id_submitbutton2').setAttribute("disabled","disabled");

    /*var procMask = "9999999-99.9999.9.99.9999";
    var proc = document.querySelector("#id_nr_process");
    if(tipo_proc==2){
      console.log(proc.value);
      VMasker(proc).unMask();
      console.log(proc.value);
    }else{
      VMasker(proc).maskPattern(procMask);
    }*/

    if(tipo_proc==0){
      var xmlHttp = new XMLHttpRequest();
      xmlHttp.open( "GET", document.getElementById('get_audiencias').value + '?nrprocesso=' + document.getElementById('id_nr_process').value, false );
      xmlHttp.send( null );
      if (window.DOMParser){
          parser = new DOMParser();
          xmlDoc = parser.parseFromString(xmlHttp.responseText, "text/xml");
      }
      else{
          xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
          xmlDoc.async = false;
          xmlDoc.loadXML(xmlHttp.responseText);
      }
      document.getElementsByName('nrprocesso')[0].value = document.getElementById('id_nr_process').value;
      document.getElementsByName('name')[0].value = document.getElementById('id_nr_process').value;
      if(typeof xmlDoc.getElementsByTagName("ns2:consultarAudienciaProcessoResponse")[0] !== 'undefined'){
        childs = xmlDoc.getElementsByTagName("ns2:consultarAudienciaProcessoResponse")[0].childNodes;

        var processo_valido = 1;
        if(typeof childs[5] === 'undefined') {
          if(typeof childs[1] === 'undefined'){
            document.getElementById('id_openingtime_enabled').checked = false;

            document.getElementById('nome_audiencia').innerHTML = '';
            document.getElementsByName('tipoaudiencia')[0].value = '';
            processo_valido = 0;
            alert('O processo informado não foi localizado');
          }else{
            alert('Processo sem audiência designada');
          }
          //Aqui caso eventualmente precisarmos tratar a mensagem enviada quando um processo não tem audiência
          //tratando o xml para inserir as tags certinho
          // var el2 = document.createElement( 'html' );
          // el2.innerHTML = childs[4].innerHTML;
          // var xml2_comtags = el2.getElementsByTagName('body')[0].innerText; //aqui ele pega o xml ja com tags
          // if (window.DOMParser){
          //     parser3 = new DOMParser();
          //     xmlDoc3 = parser3.parseFromString(xml2_comtags, "text/xml");
          // }
          // else{
          //     xmlDoc3 = new ActiveXObject("Microsoft.XMLDOM");
          //     xmlDoc3.async = false;
          //     xmlDoc3.loadXML(xml2_comtags);
          // }
          // console.log(xmlDoc3);

        }else{
          document.getElementById('nome_audiencia').innerHTML = '';
          document.getElementsByName('tipoaudiencia')[0].value = '';

          var dia = childs[4].innerHTML.substr(0,2);
          var mes = childs[4].innerHTML.substr(2,2);
          var ano = childs[4].innerHTML.substr(4,4);
          var hora = childs[4].innerHTML.substr(8,2);
          var min = childs[4].innerHTML.substr(10,2);
          var seg = childs[4].innerHTML.substr(12,2);
          var thisDateF = ano + '-' + mes + '-' + dia + 'T' + hora + ':' + min + ':' + seg;
          var mesSimple = parseInt(mes);
          var diaSimple = parseInt(dia);
          var horaSimple = parseInt(hora);
          var minSimple = parseInt(min);
          var datebegin = new Date(thisDateF);
          var today_date = new Date();
          if(datebegin.getTime()>=today_date.getTime()){
            document.getElementById('id_openingtime_enabled').checked = true;
            document.getElementById('id_openingtime_day').value = diaSimple+'';
            document.getElementById('id_openingtime_month').value = mesSimple+'';
            document.getElementById('id_openingtime_year').value = ano;
            document.getElementById('id_openingtime_hour').value = horaSimple;
            document.getElementById('id_openingtime_minute').value = minSimple;

            document.getElementById('id_closingtime_enabled').checked = true;
            document.getElementById('id_closingtime_day').value = diaSimple+'';
            document.getElementById('id_closingtime_month').value = mesSimple+'';
            document.getElementById('id_closingtime_year').value = ano;
            document.getElementById('id_closingtime_hour').value = horaSimple+1;
            document.getElementById('id_closingtime_minute').value = minSimple;

          }else{
            document.getElementById('id_openingtime_enabled').checked = false;
            alert("Processo sem audiência designada");
          }

          document.getElementById('nome_audiencia').innerHTML = childs[5].innerHTML;
          document.getElementsByName('tipoaudiencia')[0].value = childs[5].innerHTML;
        }
        if(processo_valido == 1){

          var xmlHttp2 = new XMLHttpRequest();
          var data_post = new FormData();
          data_post.append('nrprocesso', document.getElementById('id_nr_process').value);
          data_post.append('id', document.getElementById('course_id').value);
          xmlHttp2.open( "POST", document.getElementById('get_process').value, true);
          //Callbacks para erro e work
          xmlHttp2.addEventListener("load", loadComplete, false);
          xmlHttp2.addEventListener("error", loadError, false);
          xmlHttp2.send( data_post );

          function loadError(evt) {
            console.log("erro ao buscar o post do get_process");
          }

          function loadComplete(evt) {
            if (window.DOMParser){
                parser = new DOMParser();
                xmlDoc2 = parser.parseFromString(xmlHttp2.responseText, "text/xml");
            }
            else{
                xmlDoc2 = new ActiveXObject("Microsoft.XMLDOM");
                xmlDoc2.async = false;
                xmlDoc2.loadXML(xmlHttp2.responseText);
            }

            xml_final = xmlDoc2.getElementsByTagName("ns2:consultarProcessoResponse")[0].childNodes;

            //convertendo em HTML com um parser do navegador para remover os &lt... etc
            var el = document.createElement( 'html' );
            el.innerHTML = xml_final[0].innerHTML;
            var xml_comtags = el.getElementsByTagName('body')[0].innerText; //aqui ele pega o xml ja com tags
            if (window.DOMParser){
                parser = new DOMParser();
                xmlDoc3 = parser.parseFromString(xml_comtags, "text/xml");
            }
            else{
                xmlDoc3 = new ActiveXObject("Microsoft.XMLDOM");
                xmlDoc3.async = false;
                xmlDoc3.loadXML(xml_comtags);
            }

            var proc = xmlDoc3.getElementsByTagName("processo")[0].childNodes;
            document.getElementsByName('segredojustica')[0].value = proc[2].innerHTML;
            document.getElementsByName('assuntoprincipal')[0].value = proc[7].innerHTML;
            document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = '';
            document.getElementById('id_welcome').value = '';
            if(proc[2].innerHTML == 'true'){
              document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = "<strong>SEGREDO DE JUSTIÇA!</strong><br><br>";
              document.getElementById('id_welcome').value = "SEGREDO DE JUSTIÇA!\n\n";
            }
            document.getElementById('id_introeditoreditable').childNodes[0].innerHTML = document.getElementById('id_introeditoreditable').childNodes[0].innerHTML+"Assunto principal: "+proc[7].innerHTML;
            document.getElementById('id_welcome').value = document.getElementById('id_welcome').value+"Assunto principal: "+proc[7].innerHTML;

            var partes = xmlDoc3.getElementsByTagName("partes")[0].childNodes;
            partes.forEach(gravaPartes);
          }

        }
      }else{
        alert('O processo informado não foi localizado');
      }
    }

}

selectProcessType = function(){

  var tipo_proc = document.getElementById('id_process_type').value;

  if(tipo_proc==0){
    bigbluebuttonbn_process_get();
  }

  var procMask = "9999999-99.9999.9.99.9999";
  var proc = document.querySelector("#id_nr_process");
  if(tipo_proc==2){
    console.log(proc.value);
    VMasker(proc).unMask();
  }else{
    VMasker(proc).maskPattern(procMask);
  }
}
