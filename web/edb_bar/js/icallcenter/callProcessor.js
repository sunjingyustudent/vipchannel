
hojo.provide("icallcenter.callProcessor");
hojo.require("hojo.io.script");


hojo.declare("icallcenter.callProcessor", null, {
    _phone: null,

    constructor: function (phone) {
        this._phone = phone;
        var evtHandle = this._phone.register("EvtRing", this, "onRing");
        this._phone._handles.push(evtHandle);
        var evtHandle = this._phone.register("EvtHangup", this, "onHangup");
        this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtDialing", this, "onDialing");
		this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtPeerStatusChanged", this, "peerStatusChanged"); 
		this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtCallStatusChanged", this, "callStatusChanged"); 
		this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtConnected", this, "EvtConnected"); 
		this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtLogon", this, "EvtLogon"); 
		this._phone._handles.push(evtHandle);
		evtHandle = this._phone.register("EvtMonitorQueue" , this, "EvtMonitorQueue");
        this._phone._handles.push(evtHandle);
    },
    onRing: function (data) {
        console.info("onRing==================")
        console.dir(data)
    	var callsheetId = data.callSheetId; //ͨ����¼id
		var agent = data.agent; //��ϯ
		var callNo = data.originCallNo;//����
		var calledNo = data.originCalledNo;//����
		var callType = data.callType; 
		var status = data.status;
		var ringTime= data.offeringTime;
		var beginTime= "";
		var endTime= "";
		var monitorFilename= "";
		hojo.byId("icallcenter.dialout.input").value = callNo;

		ajax({
			url: "/sale/telephone-user-info",   //请求地址
			type: "POST",                       //请求方式
			data: { 'telephone':callNo },        //请求参数
			dataType: "json",
			success: function (response, xml) {
                window.parent.telphonesystem_onRing_show_userinfo(callNo,response);
			},
			fail: function (status) {
                window.parent.telphonesystem_onRing_error();
			}
		});
    },
    
    onHangup: function(data) {
        console.info("onHangup=============")
		console.log(data);
		var callsheetId = data.callSheetId;
		var agent = data.agent;
		var callNo = data.originCallNo;
		var calledNo = data.originCalledNo;
		var callType = data.callType;
		var status = data.status;
		var ringTime= data.ringTime;
		var beginTime= data.beginTime;
		var endTime= data.endTime;
		var monitorFilename= data.data.MonitorFilename;
		//alert("agent:" + agent +";callNo:" + callNo+";calledNo:"+calledNo+";callType:"+callType+";status:"+status+";ringTime:"+ringTime+";beginTime:"+beginTime+";endTime:"+endTime+";monitorFilename:"+monitorFilename);
	   	var phoneJson = {
	    		Command: "Action",
	    		Action: "Hangup",
	    		ActionID: "Hangup"+Math.random(),
	    		CallsheetId: callsheetId,
	    		CallNo: callNo,
	    		CalledNo: calledNo,
	    		CallType: callType,
	    		RingTime: ringTime,
	    		Agent: agent,
	    		Status: status,
	    		BeginTime: beginTime,
	    		EndTime: endTime,
	    		MonitorFilename: monitorFilename
		};
	   	this.sendAction(phoneJson);
    },
    
    onDialing: function(data) {//坐席响铃触发
        console.info("onDialing======================")
		console.log(data);
		var callsheetId = data.callSheetId;
		var agent = "";
		var callNo = data.originCallNo;//����
		var calledNo = data.originCalledNo;//����
		var callType = data.callType;
		var status = data.status;
		var ringTime= data.offeringTime;
		var beginTime= "";
		var endTime= "";
		var monitorFilename= "";
	   	var phoneJson = {
	    		Command: "Action",
	    		Action: "Dialing",
	    		ActionID: "Dialing"+Math.random(),
	    		CallsheetId: callsheetId,
	    		CallNo: callNo,
	    		CalledNo: calledNo,
	    		CallType: callType,
	    		RingTime: ringTime,
	    		Agent: agent,
	    		Status: status,
	    		BeginTime: beginTime,
	    		EndTime: endTime,
	    		MonitorFilename: monitorFilename
		};
	   	this.sendAction(phoneJson);
    },
    
    EvtConnected: function(data) {//接听触发
        console.info("EvtConnected======================")
		var callsheetId = data.callSheetId;
		var agent = "";
		var callNo = data.originCallNo;//����
		var calledNo = data.originCalledNo;//����
		var callType = data.callType;
		var status = data.status;
		var ringTime= data.offeringTime;
		var beginTime= data.beginTime;
		var endTime= "";
		var monitorFilename= "";
	   	var phoneJson = {
	    		Command: "Action",
	    		Action: "Connected",
	    		ActionID: "Connected"+Math.random(),
	    		CallsheetId: callsheetId,
	    		CallNo: callNo,
	    		CalledNo: calledNo,
	    		CallType: callType,
	    		RingTime: ringTime,
	    		Agent: agent,
	    		Status: status,
	    		BeginTime: beginTime,
	    		EndTime: endTime,
	    		MonitorFilename: monitorFilename
		};
	   	this.sendAction(phoneJson);
    },
    
    EvtLogon: function(data) {
    	var status = data; 
	   	var phoneJson = {
	    		Command: "Action",
	    		Action: "Logon",
	    		ActionID: "Logon" + Math.random(),
	    		Status: status
		};
	   	this.sendAction(phoneJson);
    },
    
    peerStatusChanged: function(data) {
//    	var peerStatus = data; 
//	   	var phoneJson = {
//	    		Command: "Action",
//	    		Action: "Peer",
//	    		ActionID: "Peer" + Math.random(),
//	    		Status: peerStatus
//		};
//	   	this.sendAction(phoneJson);
    },
    
    callStatusChanged: function(data) {
//    	var peerStatus = data; 
//	   	var phoneJson = {
//	    		Command: "Action",
//	    		Action: "Call",
//	    		ActionID: "Call" + Math.random(),
//	    		Status: peerStatus
//		};
//	   	this.sendAction(phoneJson);
    },
    
    EvtMonitorQueue: function (queueItem) {
    	//var isMySelfQueue = false;
       // for(var i in queueItem.members){
       // 	var member = queueItem.members[i];
       // 	if(member == this._phone.sipNo) {
                  //������������Լ���
      //          isMySelfQueue = true;
       // 	}
      //  }
      //  if(isMySelfQueue == true) {
      //  	alert(queueItem.queueName); 
      //  	alert(queueItem.idleAgentCount);
      //  	alert(queueItem.queueWaitCount);
      //  } 
    },
    
    sendAction: function(json) {
    	//hojo.byId("icallcenter.iframe").src="http://localhost:15062/?json=" + hojo.toJson(json) + "&random=" + Math.floor(Math.random()*100000);
    }
    
});






function ajax(options) {
    options = options || {};
    options.type = (options.type || "GET").toUpperCase();
    options.dataType = options.dataType || "json";
    var params = formatParams(options.data);

    //创建 - 非IE6 - 第一步
    if (window.XMLHttpRequest) {
        var xhr = new XMLHttpRequest();
    } else { //IE6及其以下版本浏览器
        var xhr = new ActiveXObject('Microsoft.XMLHTTP');
    }

    //接收 - 第三步
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            var status = xhr.status;
            if (status >= 200 && status < 300) {
                options.success && options.success(xhr.responseText, xhr.responseXML);
            } else {
                options.fail && options.fail(status);
            }
        }
    }

    //连接 和 发送 - 第二步
    if (options.type == "GET") {
        xhr.open("GET", options.url + "?" + params, true);
        xhr.send(null);
    } else if (options.type == "POST") {
        xhr.open("POST", options.url, true);
        //设置表单提交时的内容类型
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(params);
    }
}
//格式化参数
function formatParams(data) {
    var arr = [];
    for (var name in data) {
        arr.push(encodeURIComponent(name) + "=" + encodeURIComponent(data[name]));
    }
    arr.push(("v=" + Math.random()).replace(".",""));
    return arr.join("&");
}


















