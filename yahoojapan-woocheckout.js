window.yconnectInit = function() {
    YAHOO.JP.yconnect.Authorization.init({
        button: {   
            format: "image",
            type: "a",
            textType:"b",
            width: 195,
            height: 25,
            className: "yconnectLoad"
        },
        authorization: {
            clientId: "dj00aiZpPXZBMjA3R1Z5dURhaSZzPWNvbnN1bWVyc2VjcmV0Jng9Nzg-",    
            redirectUri: "http://vccw.test/wp-admin/admin-ajax.php?action=mdy_yahoo_login&",
            scope: "openid email profile address",
            windowWidth: "500",
            windowHeight: "400"
        },
        onSuccess: function(res) {
            mdy_autofill(res);
        },
        onError: function(res) {

        },
        onCancel: function(res) {

        }
    });

    YAHOO.JP.yconnect.Authorization.init({
        button: {
            format: "image",
            type: "a",
            textType:"a",
            width: 196,
            height: 38,
            className: "yconnectLogin"
        },
        authorization: {
            clientId: "dj00aiZpPXZBMjA3R1Z5dURhaSZzPWNvbnN1bWVyc2VjcmV0Jng9Nzg-",    // 登録したClient IDを入力してください
            redirectUri: "http://vccw.test/wp-admin/admin-ajax.php?action=mdy_yahoo_login&", // 本スクリプトを埋め込むページのURLを入力してください
            scope: "openid email profile address",
            responseType: "code",
            state: "44Oq44Ki5YWF44Gr5L644Gv44Gq44KL77yB",
            nonce: "5YOV44Go5aWR57SE44GX44GmSUTljqjjgavjgarjgaPjgabjgog=",
            windowWidth: "500",
            windowHeight: "400"
        },
        onSuccess: function(res) {
           mdy_autofill(res);
        },
        onError: function(res) {
            
        },
        onCancel: function(res) {

        }
    });
};
(function(){
var fs = document.getElementsByTagName("script")[0], s = document.createElement("script");
s.setAttribute("src", "https://s.yimg.jp/images/login/yconnect/auth/2.0.1/auth-min.js");
fs.parentNode.insertBefore(s, fs);
})();


function mdy_autofill(res) {
    if(res.email) jQuery('[name=billing_email]').val(res.email);
    if(res.familyName) jQuery('[name=billing_last_name]').val(res.familyName);
    if(res.givenName) jQuery('[name=billing_first_name]').val(res.givenName);
    if(res.locality) jQuery('[name=billing_city]').val(res.locality);
    if(res.postalCode) jQuery('[name=billing_postcode]').val(mdy_zip_add_dash(res.postalCode));
    if(res.country) jQuery('[name=billing_country]').val(res.country.toUpperCase());
    jQuery('[name=billing_country]').trigger('change');
    if(res.region) jQuery('[name=billing_state]').val(mdy_pref_to_code(res.region));
    jQuery('[name=billing_state]').trigger('change');
}
function mdy_zip_add_dash(zip) {
    return zip.slice(0, 3) + '-' + zip.slice(3);
}

function mdy_jp_prefectures() {
    return [
        ["北海道", "JP01", "Hokkaido"],
        ["青森県", "JP02", "Aomori"],
        ["岩手県", "JP03", "Iwate"],
        ["宮城県", "JP04", "Miyagi"],
        ["秋田県", "JP05", "Akita"],
        ["山形県", "JP06", "Yamagata"],    
        ["福島県", "JP07", "Fukushima"],
        ["茨城県", "JP08", "Ibaraki"],
        ["栃木県", "JP09", "Tochigi"],
        ["群馬県", "JP10", "Gunma"],
        ["埼玉県", "JP11", "Saitama"],
        ["千葉県", "JP12", "Chiba"],
        ["東京都", "JP13", "Tokyo"],
        ["神奈川県","JP14", "Kanagawa"],
        ["新潟県", "JP15", "Niigata"],
        ["富山県", "JP16", "Toyama"],
        ["石川県", "JP17", "Ishikawa"],
        ["福井県", "JP18", "Fukui"],
        ["山梨県", "JP19", "Yamanashi"],
        ["長野県", "JP20", "Nagano"],
        ["岐阜県", "JP21", "Gifu"],
        ["静岡県", "JP22", "Shizuoka"],
        ["愛知県", "JP23", "Aichi"],
        ["三重県", "JP24", "Mie"],
        ["滋賀県", "JP25", "Shiga"],
        ["京都府", "JP26", "Kyoto"],
        ["大阪府", "JP27", "Osaka"],
        ["兵庫県", "JP28", "Hyogo"],
        ["奈良県", "JP29", "Nara"],
        ["和歌山県","JP30", "Wakayama"],
        ["鳥取県", "JP31", "Tottori"],
        ["島根県", "JP32", "Shimane"],
        ["岡山県", "JP33", "Okayama"],
        ["広島県", "JP34", "Hiroshima"],
        ["山口県", "JP35", "Yamaguchi"],
        ["徳島県", "JP36", "Tokushima"],
        ["香川県", "JP37", "Kagawa"],
        ["愛媛県", "JP38", "Ehime"],
        ["高知県", "JP39", "Kochi"],
        ["福岡県", "JP40", "Fukuoka"],
        ["佐賀県", "JP41", "Saga"],
        ["長崎県", "JP42", "Nagasaki"],
        ["熊本県", "JP43", "Kumamoto"],
        ["大分県", "JP44", "Oita"],
        ["宮崎県", "JP45", "Miyazaki"],
        ["鹿児島県","JP46", "Kagoshima"],
        ["沖縄県", "JP47", "Okinawa"]
    ];
}

function mdy_pref_to_code(pref) {
    var jp_prefectures = mdy_jp_prefectures();
    for(var i=0; i<jp_prefectures.length; i++) {
        if(jp_prefectures[i][0] == pref) {
            return jp_prefectures[i][1]; 
        }
    }
}


