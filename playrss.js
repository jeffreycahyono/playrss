var itmTmpl = _.template($('#tplItem').html());

$('#srcTab').on('click',function(e){
    $('#frmSources').show();
});


function getSources(){
    var lines = $('#sources').val().split(/\n/);
    var texts = [];
    for (var i=0; i < lines.length; i++) {
      // only push this line if it contains a non whitespace character.
      if (/\S/.test(lines[i])) {
        texts.push($.trim(lines[i]));
      }
    }
    return texts;
}

function getFeed(sources, callback){
    $.getJSON('api/rss.php', {sources : sources})
        .done(function(raw){
            if(raw.error &&  raw.error.length){
                console.log(raw.error);
            }
            callback(raw.data);
        })
        .fail(function(jqXhr){
            console.error("Error");
            console.log(jqXhr);
        });
}

function redrawFeed(){
    var src = getSources();
    if(!src.length){
        alert("Isi dulu sumber RSS!");
        return;
    }
    $('#feedList').html('<div class="text-center"><h3>Loading...</h3></div>');
    getFeed(src, function(data){
        console.log(data);
        var html = '';
        _.each(data,function(itm){
            itm.description = itm.description || itm.content || '';
            html = html + itmTmpl(itm);
        });
        html  = '<ul class="media-list">' +  html + '</ul>';
        $('#feedList').html(html);
    });

}

$('#feedTab').on('click',function(e){
    e.preventDefault();
    $('#frmSources').hide();
    redrawFeed();
});

$('#refresh').on('click',function(e){
    e.preventDefault();
    redrawFeed();
});

