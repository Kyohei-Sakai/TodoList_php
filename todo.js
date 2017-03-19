$(function() {
  'use strict';

  // Formにフォーカスを当ててすぐに入力できるようにしておく
  $('#new_todo').focus();

  // update
  $('#todos').on('click', '.update_todo', function() {
    // idを取得
    var id = $(this).parents('li').data('id');
    // ajax処理
    $.post('_ajax.php', {
      id: id,
      mode: 'update',
      token: $('#token').val()
    }, function(res) {
      if (res.state === '1') {
        $('#todo_' + id).find('.todo_title').addClass('done');
      } else {
        $('#todo_' + id).find('.todo_title').removeClass('done');
      }
    });
  });

  // delete
  $('#todos').on('click', '.delete_todo', function() {
    // idを取得
    var id = $(this).parents('li').data('id');
    // ajax処理
    if (confirm('are you sure?')) {
      $.post('_ajax.php', {
        id: id,
        mode: 'delete',
        token: $('#token').val()
      }, function() {
        // fadeOut -> remove の時はremoveを処理待ちのqueueに入れる必要がある
        $('#todo_' + id).fadeOut(500).queue(function() {
          $(this).remove();
        });
      });
    }
  });

  // create
  $('#new_todo_form').on('submit', function() {
    // tilteを取得
    var title = $('#new_todo').val();
    // ajax処理
    $.post('_ajax.php', {
      title: title,
      mode: 'create',
      token: $('#token').val()
    }, function(res) {
      // liを追加
      var $li = $('#todo_template').clone();
      $li
        .attr('id', 'todo_' + res.id)
        .data('id', res.id)
        .find('.todo_title').text(title);
      $('#todos').prepend($li.fadeIn());
      // Formにフォーカスを当てて連続で入力できるようにしておく
      $('#new_todo').val('').focus();
    });
    // 画面の遷移を防ぐ
    return false;
  });

  // delete all of done
  $('#btn').click(function() {
    // ajax処理
    $.post('_ajax.php', {
      mode: 'delete done',
      token: $('#token').val()
    }, function(res) {
      // resはdoneした全てのidの配列
      // [{id : *}, {id : *}, ..]
      // console.log(res.ids);
      $.each(res.ids, function(index, val) {
         $('#todo_' + val.id).fadeOut(500).queue(function() {
           $(this).remove();
         });
      });
    });
  });

});
