hojo.provide("icallcenter.stateElement.link.threeWayCallLink");hojo.declare("icallcenter.stateElement.link.threeWayCallLink",null,{constructor:function(a){this._base=a},_base:null,_callState:"stThreeWayTalking",_changeToolBarState:function(b){hojo.publish("EvtCallToolBarChange",[b._callState])},_switchCallState:function(c){if(c.Event=="ChannelStatus"){if(c.Exten==this._base._phone.sipNo){if(c.ChannelStatus=="Hangup"){this._base._curCallState=this._base._getInvalid();this._changeToolBarState(this._base._curCallState)}}}},_publish:function(){}})