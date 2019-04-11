function ajaxs(url,data,suc){
	$.ajax({
		//async : true,
		url,
		data,
		dataType:"json",
		type : "POST",
		timeout: 15000,
		success: function(response, status, xhr){
			// debugger
			// suc(JSON.parse(response))
			suc(response)
			var sta=xhr.status.toString()
			switch (xhr.status) {
				case '304':
					console.log('浏览器未检测到服务端有更新内容，当前请求内容为缓存读取')
					break;
				case '400':
					alert('参数错误')
					break;
				case '401':
					alert('身份未通过服务器验证')
					break;
				case '404':
					alert('请求失败，无此页面，请检查地址信息')
					break;
				default:
					break;
			}
			if(sta.slice(0,1)==5){
				alert('服务器错误')
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			debugger
			alert('请求超时，请检查当前网络状态，并刷新页面' + textStatus)
		}
	});
}
function ajaxGet(url,data,suc){
	$.ajax({
		//async : true,
		url,
		data,
		dataType:"json",
		type : "GET",
		timeout: 15000,
		success: function(response, status, xhr){
			// debugger
			// suc(JSON.parse(response))
			suc(response)
			var sta=xhr.status.toString()
			switch (xhr.status) {
				case '304':
					console.log('浏览器未检测到服务端有更新内容，当前请求内容为缓存读取')
					break;
				case '400':
					alert('参数错误')
					break;
				case '401':
					alert('身份未通过服务器验证')
					break;
				case '404':
					alert('请求失败，无此页面，请检查地址信息')
					break;
				default:
					break;
			}
			if(sta.slice(0,1)==5){
				alert('服务器错误')
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			debugger
			alert('请求超时，请检查当前网络状态，并刷新页面' + textStatus)
		}
	});
}

function timeLV(num){
	var timestamp4 = new Date(num);//直接用 new Date(时间戳) 格式转化获得当前时间

	console.log(timestamp4);

	var time=timestamp4.toLocaleDateString().replace(/\//g, "-") + " " + timestamp4.toTimeString().substr(0, 8)
	console.log(time);
	return time
}

// 获取省市区
function province(){
	var url ='http://pos1.123.com/api/apipos/province'
	var data={}
	ajaxs(url,data,(res)=>{
		$('#province').empty()
		for(let item of res.data){
			$('#province').append(`<option value ="${item.region_id}" >${item.region_name}</option>`)
			}
	})
}
function citY(e){
	var url ='http://pos1.123.com/api/apipos/city'
	var data={
		id:e
	}
	ajaxs(url,data,(res)=>{
		$('#city').empty()
		for(let item of res.data){
				$('#city').append(`<option value ="${item.region_id}" onclick=''>${item.region_name}</option>`)
			}
	})
}
function countY(e){
	var url ='http://pos1.123.com/api/apipos/area'
	var data={
		id:e
	}
	ajaxs(url,data,(res)=>{
		$('#county').empty()
		for(let item of res.data){
			$('#county').append(`<option value ="${item.region_id}" onclick=''>${item.region_name}</option>`)
			}
	})
}
// 获取开户行列表
function newC(){
	var url ='http://192.168.1.161/api/apipos/banklist'
	var data={}
	ajaxs(url,data,(res)=>{
		$('#place').empty()
		for(let item of res.data){
				$('#place').append(`<option value ="${item.id}">${item.name}</option>`)
			}
	})
}