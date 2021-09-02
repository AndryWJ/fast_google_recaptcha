var FGR_api = (function(){

    function listenerFormSubmit(e){
        e.stopPropagation(); e.preventDefault();//Відміняємо дію за замовчуванням і не розповсюджуємо подію
        var input_token = e.srcElement.querySelector('[name="frg_token"]');//input форми яку відправили
        execute([input_token],true);//Вставимо новий токен перед відправкою
    }

    function execute(list_inputs,form_refresh = false){
        if(list_inputs == undefined){
            var list_inputs = document.querySelectorAll('[name="frg_token"]');
        }
        grecaptcha.execute(frg_data.fgr_sitekey, {action: 'submit'}).then(function(token){
            // Проходимось циклом по input
            list_inputs.forEach(function(input){
                input.value = token;//вставляємо токен
                var form = input.closest('form');//Вибираємо форму
                form.removeEventListener('submit', listenerFormSubmit, true);//Щоб повторно не встановлювався обробник подій, скільки б раз користувач не викликав подію оновлення токена (можливо пригодиться)
                if(form_refresh){//Якщо це оновлення перед відправкою то згенеруємо подію щоб інші обробники подій могли відпрацювати
                    var event = new Event('submit', {
                        'bubbles'    : true,
                        'cancelable' : true 
                    });
                    form.dispatchEvent(event);
                }
                form.addEventListener('submit', listenerFormSubmit, true);
            });
        });
    }

    var public = {
        "set_response" : function(){//Заповнить токенами тільки якщо до цього там було порожньо
            var list_inputs = document.querySelectorAll('[name="frg_token"][value=""]');
            execute(list_inputs);
        },
        "refrash_response" : function(){//обновить токени в полях в будьякому випадку
            execute();
        },
        "init" : function(){
            grecaptcha.ready(function(){ execute(); });
        }
    };

    return public;
})();


document.addEventListener('DOMContentLoaded', function () {
    FGR_api.init();
});