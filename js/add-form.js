var addTrustForm,TR_element_count = 0;
(function(a){
	addTrustForm =
	{
		init:function()
		{
			var l,z,v,x="",s,m;

			postboxes.add_postbox_toggles("trustform");
			a('.if-js-closed').removeClass('if-js-closed').addClass('closed');

			a('#tab').tabs({ fx: { duration: 'fast', opacity: 'toggle' } });

			a('#trust-form-title').hover(
                function(){a(this).removeClass("trust-form-title");},
				function(){a(this).addClass("trust-form-title");}
			);

			a('textarea').bind('textchange', function(){
				a(this).html(a(this).val());
			});

			a('#css-editor').dialog({
  				autoOpen: false,
       			title: "CSS Editor",
        		minWidth: 450,
        		minHeight: 500,
        		position: [522,296]
			});

			a("#menu-css_editor").on('click', function(){ a("#css-editor").dialog("open"); });

/*
			a('#require-mark').dialog({
  				autoOpen: false,
       			title: "Require Mark",
        		minWidth: 300,
        		minHeight: 120,
        		position: [570,315]//changed by natasha->shippai
			});

			a("#menu-require_mark").on('click', function(){ a("#require-mark").dialog("open"); });
*/
			// changed by kamiyan
			a('#require-mark').dialog({
				autoOpen: false,
				title: "Require Mark",
				minWidth: 300,
				minHeight: 120,
				position: {
					of : '#menu-require_mark',
					at: 'right bottom',
					my: 'left top'
				},
				buttons: {
					"OK": function () {
						a(this).dialog("close");
					}
				}
			});

			a("#menu-require_mark").on('click', function(){ a("#require-mark").dialog("open"); });

			a("#require-mark-setting").on('click', function(){
				if (a(this).is(":checked")) {
					addTrustForm.setRequireMark(a);
				} else {
					addTrustForm.removeRequireMark(a);
				}
			});

			a('#css-content-editor').bind('textchange', function(){
				a('#front-css').text(a(this).val());
			});

			a("#require-mark-text").on('textchange', function(){
				a("#require-mark-content > span").html(a(this).val());
					addTrustForm.setRequireMark(a);
			});
			//自動返信メール部分の開閉
			if ( a("input[name=user_mail_y]").is(":checked") ) {
				a("#reply-table").css("display", "block");
			} else {
				a("#reply-table").css("display", "none");
			}
			a("input[name=user_mail_y]").on('click', function(){
				if ( a("input[name=user_mail_y]").is(":checked") ) {
					a("#reply-table").show();
				} else {
					a("#reply-table").hide();
				}
			});

			addTrustForm.setup(a);

			// 左の要素テーブルのドラッガブルイベント
			a("#standard-form-element-list > tbody > tr.form-element").draggable({
				connectToSortable : "table.element-sortables > tbody",
                distance : 2,
                helper : "clone",
                zIndex : 5,
                containment : "document",
                stop : function(e, dg){
                    a("#setting-form > tbody > tr").removeClass("form-element");
                    a("#setting-form > tbody > tr > .element-title").remove();
                    a("#setting-form > tbody > tr > .setting-element-title").css("visibility", "visible");
                    a("#setting-form > tbody > tr > .setting-element-discription").css("visibility", "visible");
                    //added by natasha
                    a('#setting-description').css('display', 'block');
                }
			});

			addTrustForm.sortable(a);

			/*
			function readyEdit(){

				//各要素のタイトルを編集するためのクリックイベント
				var elementTitle = ".setting-element-title > div.subject > span.content, .setting-element-title > div.submessage > span.content";
				if (!a(elementTitle).children("input").length) {
					a(elementTitle).removeClass('subject-hover');
					a(elementTitle).html(a('<input>',{type:'text',val:a(this).html()}));
					a(elementTitle).children("input").focus().select().blur(function(){
						a(elementTitle).parent().html(a(elementTitle).val());
						//確認画面へも要素を反映(パフォーマンスが悪くなれば、アルゴリズムを変える)
						addTrustForm.asyncForm();
					});
				}
			}
*/


			//各要素のタイトルを編集するためのクリックイベント
			a(".setting-element-title > div.subject > span.content, .setting-element-title > div.submessage > span.content").live("click", function(){
				if (!a(this).children("input").length) {
					a(this).removeClass('subject-hover');
					a(this).html(a('<input>',{type:'text',val:a(this).html()}));
					a(this).children("input").focus().select().blur(function(){
						a(this).parent().html(a(this).val());
						//確認画面へも要素を反映(パフォーマンスが悪くなれば、アルゴリズムを変える)
						addTrustForm.asyncForm();
					});
				}
			});

			//各要素の編集メニューを表示させるクリックイベント
			a(".edit-button").live("click", function(){
				var p = a(this).nextAll(".text-edit-content");
				var hi = a(this).closest('td').height();
				p.toggleClass('display-out').draggable();
				a(this).closest('.edit-element-container').height(hi);
				a(".del-icon").live("click", function(){
					p.addClass('display-out');
				});
			});

			//要素の削除機能
			a(".delete-button").live("click", function(){
				var q = a(this).closest("tr.ui-draggable");
				q.css("background-color", "#FF0000").fadeOut("slow");
				q.queue(function () {
					q.remove();
					//確認画面へも要素を反映
					addTrustForm.asyncForm();
					if (!a("#setting-form").find("tr.ui-draggable").length) {
						a("#first-setting-info").unbind();
      					a("#first-setting-info").css("display","block");
      				}
					q.dequeue();
      			});
			});

			//要素タイトルのinput内を塗りつぶす
			a(".setting-element-title").live("mouseover", function(){
				a(this).addClass("element-title-hover");
			});

			//バリデーション設定のボックスのスライド
			a("input[name=textbox-char-num]").live("click", function(){
				a(this).parent().next().slideToggle("fast");
			});

			//バリデーション設定のボックスのスライド
			a("input[name=textbox-multi-characters]").live("click", function(){
				if ( a("input[name=textbox-characters]").is(":checked") ) {
					a("input[name=textbox-characters]").parent().next().slideUp("fast");
					a("input[name=textbox-characters]").prop('checked', false);
				}
				a(this).parent().next().slideToggle("fast");
			});

			//バリデーション設定のボックスのスライド
			a("input[name=textbox-characters]").live("click", function(){
				if ( a("input[name=textbox-multi-characters]").is(":checked") ) {
					a("input[name=textbox-multi-characters]").parent().next().slideUp("fast");
					a("input[name=textbox-multi-characters]").prop('checked', false);
				}
				a(this).parent().next().slideToggle("fast");
			});
			//バリデーション設定のボックスのスライド
			a("input[name=textarea-char-num]").live("click", function(){
				a(this).parent().next().slideToggle("fast");
			});

			//inputとtextareaにフォーカスを当てる
			a("#setting-form").find("input,textarea").live("click", function(){
				a(this).focus();
			});

			//バリデーション設定反映
			a("input[type=checkbox]").live("click", function(){
				if (a(this).is(':checked')){
					a(this).attr('checked', 'checked');
				} else {
					a(this).removeAttr('checked');
				}
			});

			a("input[type=radio]").live("click", function(){
				a("input[name="+a(this).attr('name')+"]").removeAttr('checked');
				a(this).attr('checked', 'checked');
			});

		},
		changeElement: function(g,t,c,x)
		{
			var v = g.val();
			if ( c === "checkbox" || c === "radio" ) {
				g.closest("tr.ui-draggable").find(".setting-element-discription > ul > li >"+t).attr(x, v);
			} else if ( c === "button" || c === "image" ) {
				g.children().attr(x, a("input[name=submitbutton-text]").val());
			} else {
				g.closest("tr.ui-draggable").find(".setting-element-discription > "+t).attr(x, v);
			}
			g.attr('value', v);
		},
		toggleHTMLEditor: function(t)
		{

			v = t.children("textarea").val();
			v = v.replace(/\r\n/g, '<br />');
			v = v.replace(/(\n|\r)/g, '<br />');
			t.html(v);
		},
		//確認画面へフォームを反映する
		asyncForm: function(){
			x = "";
			a("#setting-form > tbody > tr").not("#first-setting-info").each(function(){
				x = x + '<tr><th><div class="subject">'+a(this).children('th').children('div.subject').children('span.content').html()+'</div></th><td>entered word</td></tr>';
			});
			a("#setting-confirm-form > tbody").html(x);
		},

		setup: function(a){
			addTrustForm.textContentEvent(a);
			addTrustForm.setupButton(a);
		},

		setupForm: function(a){
			var w="option value";
			a("#setting-form > tbody > tr").hover(
                function(){
                	a(this).addClass("element-hover");
                },
				function(){a(this).removeClass("element-hover");}
			);
			//必須属性の対応
			a("#setting-form > tbody > tr").not("#first-setting-info").each(function(){
				a(this).find("input[name="+a(this).attr("title")+"-required]").on('click', function(){
					if(a(this).is(":checked") && a("#require-mark-text").val() != ''){
						a(this).closest('tr').find("div.subject > span.require").html(a("#require-mark-content > span").html());
					} else {
						a(this).closest('tr').find("div.subject > span.require").html('');
					}
				});
			});

			//trエレメントの編集モードを解除
			a('#setting-form').find('tr').outerClick(function(){
				if (a(this).find(".text-edit-content").css("display") !== "block") {
					a(this).removeClass("element-hover-edit");
					a(this).children(".setting-element-editor").children(".edit-element-container").css("display", "none");
					a(this).children(".setting-element-editor").css("display", "none");
				} else {
					a(this).find(".text-edit-content").addClass('display-out');
				}

				if (a(this).find('div.subject > span.content, div.submessage > span.content').children("input").length) {
					a(this).find('div.subject > span.content, div.submessage > span.content').children("input").blur();
				}
			});

			//trエレメントを編集モードにする
			a('#setting-form').find('tr').bind('click', function(){
				a("#setting-form > tbody > tr").removeClass("element-hover-edit").children(".edit-element-container").css("display", "none");
				a(this).addClass("element-hover-edit");
				a(this).children(".setting-element-editor").css("display", "block");
				a(this).children(".setting-element-editor").children(".edit-element-container").css("display", "block");

				s = a(this).find('div.subject > span.content, div.submessage > span.content');
				if (!s.children("input").length) {
					a(this).find('div.subject > span.content').html(a('<input>',{type:'text',title:'edit title',value:a(this).find('div.subject > span.content').html()}));
					a(this).find('div.submessage > span.content').html(a('<input>',{type:'text',title:'edit attention message',value:a(this).find('div.submessage > span.content').html()}));
					s.children("input").ahPlaceholder({
         				placeholderColor : 'silver'
					});
					s.children("input").focus(function(){
						a(this).select();
					});
					s.children("input").blur(function(){
						if (a(this).attr('title') == a(this).val()) {
							a(this).parent().html('');
						} else {
							a(this).parent().html(a(this).val());
						}
						//確認画面へも要素を反映(パフォーマンスが悪くなれば、アルゴリズムを変える)
						addTrustForm.asyncForm();
					});
				}
			});
			//セレクトボックス、チェック、ラジオのオプション値をリアルタイムに反映
			a('#setting-form').find("textarea.option-value-editor").bind('focus',function(){
				var t = a(this),r = t.attr('role'),q;

				if (r !== 'selectbox') {
					q = setInterval(function(){

						var p = t.val(), tmp = '', name=t.closest('tr').find(".setting-element-discription > ul > li > input:first").attr('name');

						p = p.replace(/\r/g, '');
						p = p.split(/\n/g);

						for (var i=0;i<p.length;i++){
							tmp += '<li><input type="'+r+'" name="'+name+'" value="'+p[i]+'" />'+ p[i]+'</li>'
						}

						t.closest('tr').find(".setting-element-discription > ul").html( tmp );

					} ,200);
				} else {
					q = setInterval(function(){

						var p = t.val(), tmp = '';

						p = p.replace(/\r/g, '');
						p = p.split(/\n/g);

						for (var i=0;i<p.length;i++){
							tmp += '<option value="'+p[i]+'" >'+ p[i]+'</option>'
						}
						tmp = '<option value="">'+w+'</option>'+tmp
						t.closest('tr').find(".setting-element-discription > select").html( tmp );
					} ,200);
				}

				a(this).blur(function(){
					clearInterval(q);
				});
			});

			//テキスト要素のサイズをリアルタイム反映
			a("input[name=textbox-size]").bind("textchange", function(){
				if(!isNaN(a(this).val())) {
					addTrustForm.changeElement(a(this) ,"input","text" ,"size");
				}
			});

			//テキスト要素のマックスレングスをリアルタイム反映
			a("input[name=textbox-maxlength]").bind("textchange", function(){
				if(!isNaN(a(this).val())) {
					addTrustForm.changeElement(a(this) ,"input","text", "maxlength");
				}
			});

			//テキスト要素のクラスをリアルタイム反映
			a("input[name=textbox-class]").bind("textchange", function(){
				addTrustForm.changeElement(a(this) ,"input", "text", "class");
			});

			//テキストエリア要素のrowsをリアルタイム反映
			a("input[name=textarea-rows]").bind("textchange", function(){
				if(!isNaN(a(this).val())) {
					addTrustForm.changeElement(a(this) ,"textarea", "textarea","rows");
				}
			});

			//テキストエリア要素のcolsをリアルタイム反映
			a("input[name=textarea-cols]").bind("textchange", function(){
				if(!isNaN(a(this).val())) {
					addTrustForm.changeElement(a(this) ,"textarea", "textarea","cols");
				}
			});

			//テキストエリア要素のclassをリアルタイム反映
			a("input[name=textarea-class]").bind("textchange", function(){
				addTrustForm.changeElement(a(this) ,"textarea", "textarea","class");
			});

			//チェックボックス要素のclassをリアルタイム反映
			a("input[name=checkbox-class]").bind("textchange", function(){
				addTrustForm.changeElement(a(this) ,"input", "checkbox","class");
			});

			//ラジオボタン要素のclassをリアルタイム反映
			a("input[name=radio-class]").bind("textchange", function(){
				addTrustForm.changeElement(a(this) ,"input", "radio","class");
			});

			//セレクトボックス要素のclassをリアルタイム反映
			a("input[name=selectbox-class]").bind("textchange", function(){
				addTrustForm.changeElement(a(this) ,"select", "selectbox","class");
			});

			//セレクトボックスの選択肢のデフォルト値をリアルタイム反映
			a("input[name=selectbox-default-value]").bind("textchange", function(){
				w = a(this).val();
				a(this).closest("tr").find(".setting-element-discription > select").children("option:first").text(w);
			});

			//テキストボックスへの入力値をvalueに反映
			a("input[type=text]").bind("textchange", function(){
				a(this).attr('value', a(this).val());
			});

			//テキストエリアへの入力値をvalueに反映
			a('textarea').bind('textchange', function(){
				a(this).html(a(this).val());
			});
		},
		sortable: function(a) {
			//input form右のフォームを作る箇所のソータブルイベント
			a("table.element-sortables > tbody").sortable({
				cursor : "move",
				distance : 2,
				containment : "#tab-1",
				placeholder : "sort-hover",
				tolerance : "pointer",
				opacity : 0.7,
				cancel: "#first-setting-info",
				receive: function(e,ui){
					// setTimeout(function(){
					// 	a('#setting-form').find('tr').each(function(){
					// 		a(this).click();
					// 	});
					// },500);
				},
				helper : function() {
					return a('<tr><td></td><td></td></tr>');
				},
				sort : function() {
					a(".sort-hover").html(a('<td colspan="2" class="sort-hover"></td>'));
				},
				activate : function(e,ui) {
					l = a("#setting-form > tbody").children("tr").not("#first-setting-info").length;
					z = a(ui.item).prevAll("tr").length;
				},
				stop : function(e, ui){
					a(ui.item).find('div.submessage > span.content').hover(
                		function(){
                			//a(this).addClass("subject-hover");
                			if (a(this).html() == '' ) {
                				a(this).text('entry attention message');
                			}
                		},
						function(){
							//a(this).removeClass("subject-hover");
							if (a(this).html() == 'entry attention message' ) {
                				a(this).text('');
                			}
						}
					);
					//必須属性
					a(this).find("input[name="+a(ui.item).attr("title")+"-required]").on('click', function(){
						if(a(this).is(":checked") && a("#require-mark-text").val() != ''){
							a(ui.item).find("div.subject > span.require").html(a("#require-mark-content > span").html());
						} else {
							a(ui.item).find("div.subject > span.require").html('');
						}
					});

					//各要素にheightをセット。編集メニュが現れたときに伸びないようにするため
					a(ui.item).children("td").height(a(ui.item).children("td").height());
					a(ui.item).children("th").height(a(ui.item).children("th").height());

					//初期メッセージの削除
					a("#first-setting-info").length ? a("#first-setting-info").css("display","none") : '';

					//要素が追加された場合
					if (l < a("#setting-form > tbody").children("tr").length) {
						//input等の要素を出現させる
						a(ui.item).find(".setting-element-discription").children().removeAttr('style');

						//name属性を付加
						if ( a(ui.item).find(".setting-element-discription").find('input').attr('type') != 'checkbox' ) {
							a(ui.item).find(".setting-element-discription").find('input,select,textarea').attr('name',"element-"+TR_element_count);
							a(ui.item).find("input[name=akismet-config]").attr( 'name', 'akismet-config-element-'+ TR_element_count);
							a(ui.item).find("input[name=textbox-character]").attr( 'name', 'textbox-character-element-'+ TR_element_count);
							a(ui.item).find("input[name=textbox-multi-character]").attr( 'name', 'textbox-multi-character-element-'+ TR_element_count);

						} else {
							a(ui.item).find(".setting-element-discription").find('input').attr('name',"element-"+TR_element_count+"[]");
						}
						TR_element_count++;
						addTrustForm.setupForm(a);
					}
					//確認画面へも要素を反映
					addTrustForm.asyncForm();
				}
			});
		},

		textContentEvent: function(a) {
			var  d = 'stop',j = 'stop', b = 'stop';

			//input form 上部のHTML,下部submitに対するホバー
			/* changed by natasha
			a("#info-message-input,#info-message-confirm,#info-message-finish,#message-container-input,#message-container-confirm,#message-container-finish,.submit-container").hover(
				function(){a(this).addClass("element-hover");},
				function(){a(this).removeClass("element-hover");}
			);
			*/
			a("#info-message-input,#info-message-confirm,#info-message-finish,.submit-container").hover(
				function(){a(this).addClass("element-hover");},
				function(){a(this).removeClass("element-hover");}
			);

			//input form 上部のHTMLに対するアウタークリック
/*			a('#message-container-input').outerClick(function(){
				if (a(this).children("textarea").length && d === 'stop' ) {
					if (a(this).children("textarea").val()){
						v = a(this).children("textarea").val();
						v = v.replace(/\r\n/g, '<br>');
						v = v.replace(/(\n|\r)/g, '<br>');
						a(this).html(v);
					} else {
						a(this).prev().css("display", "block");
						a(this).css("display", "none");
					}
				}
				d = 'stop';
			});

			//confirm form 上部のHTMLに対するアウタークリック
			a("#message-container-confirm").outerClick(function(){
				if (a(this).children("textarea").length && j === 'stop' ) {
					if (a(this).children("textarea").val()){
						v = a(this).children("textarea").val();
						v = v.replace(/\r\n/g, '<br>');
						v = v.replace(/(\n|\r)/g, '<br>');
						a(this).html(v);
					} else {
						a(this).prev().css("display", "block");
						a(this).css("display", "none");
					}
				}
				j = 'stop';
			});

			//finish form 上部のHTMLに対するアウタークリック
			a("#message-container-finish").outerClick(function(){
				if (a(this).children("textarea").length && b === 'stop' ) {
					if (a(this).children("textarea").val()){
						v = a(this).children("textarea").val();
						v = v.replace(/\r\n/g, '<br>');
						v = v.replace(/(\n|\r)/g, '<br>');
						a(this).html(v);
					} else {
						a(this).prev().css("display", "block");
						a(this).css("display", "none");
					}
				}
				b = 'stop';
			});
*/
			//input form 上部のHTMLに対するクリックイベント（初期メッセージ）
			a("#info-message-input").bind("click",function(){
				d = 'start';
				a(this).next().css("display", "block");
				a(this).next().html(a('<textarea>', {cols:40}));
				a(this).next().children('textarea').bind('textchange', function(){
					a(this).html(a(this).val());
				});
				a(this).css("display", "none");
			});

			//confirm form 上部のHTMLに対するクリックイベント（初期メッセージ）
			a("#info-message-confirm").bind("click",function(){
				j = 'start';
				a(this).next().css("display", "block");
				a(this).next().html(a('<textarea>', {cols:40}));
				a(this).next().children('textarea').bind('textchange', function(){
					a(this).html(a(this).val());
				});
				a(this).css("display", "none");
			});

			//finish form 上部のHTMLに対するクリックイベント（初期メッセージ）
			a("#info-message-finish").bind("click",function(){
				b = 'start';
				a(this).next().css("display", "block");
				a(this).next().html(a('<textarea>', {cols:40}));
				a(this).next().children('textarea').bind('textchange', function(){
					a(this).html(a(this).val());
				});
				a(this).css("display", "none");
			});

			//input form 上部のHTMLに対するクリックイベント
			/* deleted by natasha
			a("#message-container-input,#message-container-confirm,#message-container-finish").bind("click",function(){
				if(!a(this).children("textarea").length) {
					v = a(this).html();
//					v = v.replace(/<br>/g, "\r\n");
					a(this).html(a('<textarea>', {cols:40,value:v}));
					a(this).children('textarea').html(a(this).children('textarea').val());
					a(this).children('textarea').bind('textchange', function(){
						a(this).html(a(this).val());
					});
				}
			});
			*/

		},
		setupButton: function(a){
			var r = 'stop',p = "stop";
			//input formサブミットボタンのアウタークリック。編集状態を解除する
			a("#confirm-button").next().outerClick(function(){
				if (a(this).css("display") === "block" && r === 'stop') {
					a(this).css("display", "none");
					a(".submit-container").removeClass("element-hover-edit");
				}
				r = 'stop';
			});

			//input formサブミットボタンの☓ボタン押下時。編集状態を解除する
			/*
			a("#confirm-button").next().find(".del-icon").bind("click",function(){
				if (a(this).css("display") === "block" && r === 'stop') {
					a(".submit-element-container").css("display", "none");
					a(".submit-container").removeClass("element-hover-edit");
				}
				r = 'stop';
			});
			*/
			/*
			a("#confirm-button").closest(".del-icon").bind("click",function(){
				alert("wow");
				if (a(this).css("display") === "block" && r === 'stop') {
					a(".submit-element-container").css("display", "none");
					a(".submit-container").removeClass("element-hover-edit");
				}
				r = 'stop';
			});
			*/
			
			a(".submit-icon").bind("click",function(){
				if (a("#confirm-button").css("display") === "block" && r === 'stop') {
					a(".submit-element-container").css("display", "none");
					a(".submit-container").removeClass("element-hover-edit");
				}
				r = 'stop';
			});

			//input formサブミットボタンのイベント設定
			a("#confirm-button").bind("click",function(){
				a(this).addClass("element-hover-edit");
				a(this).nextAll(".submit-element-container").css("display", "block");
				a(this).nextAll(".submit-element-container").css("left", (a(this).nextAll(".submit-element-container").width()-40)+"px");
				r = 'start';
			});

			//submitボタンのメッセージ変更。画像の場合はオルト
			a("input[name=submitbutton-text]").bind("textchange", function(){
				if (a("#confirm-button").children().attr("type") === "submit") {
					addTrustForm.changeElement(a("#confirm-button") ,"input", "button", "value");
				}else{
					addTrustForm.changeElement(a("#confirm-button") ,"input", "image", "alt");
				}
			});

			//メディアアップロータAPI呼び出し
			a('a.media-upload').each(function(){
				a(this).click(function(){
					var rel = a(this).attr("rel"),d=a(this);
					window.send_to_editor = function(html) {
						imgurl = a('img', html).attr('src');
						if (d.closest(".submit-element-container").prev("#confirm-button").length) {
							d.closest(".submit-element-container").prev().html(a('<input>',{type:'image',
																							src:imgurl,
																							name:"send-to-confirm",
																							alt:a("[name=submitbutton-text]").val()}));
						} else if (d.attr("id") === "return-button-select-file") {
							a("input[name=return-to-input]").removeAttr("value").prop({type:'image',
																						src:imgurl,
																						name:"return-to-input",
																						alt:a("input[name=returnbutton-text]").val()});
						} else if (d.attr("id") === "send-button-select-file") {
							a("input[name=send-to-finish]").removeAttr("value").prop({type:'image',
																						src:imgurl,
																						name:"send-to-finish",
																						alt:a("input[name=sendbutton-text]").val()});
						} else if (d.attr("id") === "require-mark-image") {
							a("#require-mark-content > span").html(a('<img>', {src:imgurl}));
							a('#require-mark-text').val('<img src="'+imgurl+'" />');
							addTrustForm.setRequireMark(a);
						}
						d.next().css("display", "block");
						tb_remove();
					}
					tb_show(null, 'media-upload.php?post_id=0&type=image&TB_iframe=true');
					return false;
				});
			});

			//input submitボタンを画像からボタンに戻す処理
			a("input[name=restore-to-button]").bind("click", function(){
				var v = a("input[name=submitbutton-text]").val();
				a("input[name=send-to-confirm]").prop({type:"submit",value:v != "" ? v : "Confirm" });
				a(this).css("display", "none");
			});

			//confirm returnボタンを画像からボタンに戻す処理
			a("input[name=restore-to-return-button]").bind("click", function(){
				var v = a("input[name=returnbutton-text]").val();
				a("input[name=return-to-input]").prop({type:"button",value:v != "" ? v : "return" });
				a(this).css("display", "none");
			});

			//confirm sendボタンを画像からボタンに戻す処理
			a("input[name=restore-to-send-button]").bind("click", function(){
				var v = a("input[name=sendbutton-text]").val();
				a("input[name=send-to-finish]").prop({type:"submit",value:v != "" ? v : "send" });
				a(this).css("display", "none");
			});

			//confirm formサブミットボタンのアウタークリック。編集状態を解除する
			a("#finish-button").next().outerClick(function(){
				if (a(this).css("display") === "block" && p === 'stop') {
					a(this).css("display", "none");
					a("#finish-button").removeClass("element-hover-edit");
				}
				p = 'stop';
			});

			//confirm formサブミットボタンの☓ボタン押下時。編集状態を解除する
			a("#finish-button").next().find(".del-icon").click(function(){
				if (a(this).css("display") === "block" && p === 'stop') {
					a(".submit-element-container").css("display", "none");
					a(".submit-container").removeClass("element-hover-edit");
				}
				p = 'stop';
			});

			//confirm formサブミットボタンのイベント設定
			a("#finish-button").click(function(){
				a(this).addClass("element-hover-edit");
				a(this).nextAll(".submit-element-container").css("display", "block");
				a(this).nextAll(".submit-element-container").css("left", (a(this).nextAll(".submit-element-container").width()-40)+"px");
				p = 'start';
			});

			//confirm returnボタンのメッセージ変更。画像の場合はオルト
			a("input[name=returnbutton-text]").bind("textchange", function(){
				if (a("input[name=return-to-input]").attr("type") === "submit") {
					a("input[name=return-to-input]").val(a(this).val());
				}else{
					a("input[name=return-to-input]").prop("alt", a(this).val());
				}
			});

			//confirm returnボタンのメッセージ変更。画像の場合はオルト
			a("input[name=sendbutton-text]").bind("textchange", function(){
				if (a("input[name=send-to-finish]").attr("type") === "submit") {
					a("input[name=send-to-finish]").val(a(this).val());
				}else{
					a("input[name=send-to-finish]").prop("alt", a(this).val());
				}
			});
		},
		setRequireMark: function(a){
			a("#setting-form > tbody > tr").not("#first-setting-info").each(function(){
				if (a(this).find("input[name="+a(this).attr("title")+"-required]").is(":checked")) {
					a(this).find("div.subject > span.require").html(a("#require-mark-content > span.require").html());
				}

			});
		},
		removeRequireMark: function(a){
			a("#setting-form > tbody > tr").not("#first-setting-info").each(function(){
				if (a(this).find("input[name="+a(this).attr("title")+"-required]").is(":checked")) {
					a(this).find("div.subject > span.require").html('');
				}

			});
		},
	},
	a(document).ready(function ()
    {
        addTrustForm.init();
    })
})(jQuery);