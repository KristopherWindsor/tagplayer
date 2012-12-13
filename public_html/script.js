var manager = new function(){
	var _this = this;
	
	this.dir = 'desc';
	this.field = 'tag';
	this.reqCounter = 0;
	this.currentSong = 0;
	this.playRequest = null;
	
	this.init = function(){
		this.setSort();
		$('#query').on('keyup paste change', this.requestData);
		$('th a').click(this.setSort);
	};
	
	this.setSort = function(event){
		if (event){
			var field = $(event.target).closest('th').data('field');
			if (field == _this.field)
				_this.dir = (_this.dir == 'asc' ? 'desc' : 'asc');
			else
				_this.dir = 'asc';
			_this.field = field;
		}
		
		$('th span').removeClass();
		$('th[data-field="' + _this.field + '"] span').addClass('arrow').addClass(_this.dir);
		
		_this.requestData();
	};
	
	this.requestData = function(){
		$.post('get.php', {query: $('#query').val(), sort_col: _this.field, sort_dir: _this.dir, echo: ++_this.reqCounter}, _this.receiveData);
	};
	
	this.receiveData = function(data, textStatus, jqXHR){
		if (_this.reqCounter != jqXHR.getResponseHeader('echo'))
			return;
		
		// templates would be nice
		var tbody = $('tbody').html('');
		for (var i in data){
			var row = $('<tr>');
			$('<td>').text(data[i].title).appendTo(row);
			$('<td>').text(data[i].artist).appendTo(row);
			$('<td>').text(data[i].album).appendTo(row);
			$('<td>').text(data[i].tags).appendTo(row);
			row.appendTo(tbody);
		}
		
		if (data){
			if (_this.playRequest)
				clearTimeout(_this.playRequest);
			var go = function () {_this.playUrl(data[0].id, data[0].url); _this.playRequest = null;}
			_this.playRequest = setTimeout(go, 1000);
		}
	};
	
	this.playUrl = function(songId, url){
		if (!songId || songId == _this.currentSong)
			return;
		
		if (_this.currentSong)
			$('#track' + _this.currentSong).hide().trigger('pause');
		_this.currentSong = songId;
		
		if ($('#track' + songId).length)
			$('#track' + songId).show().trigger('play');
		else {
			var player = $('<audio controls autoplay>').attr('id', "track" + songId).attr('src', url);
			$('#audio').append(player);
		}
	};
};

$(document).ready(function (){
	manager.init();
});
