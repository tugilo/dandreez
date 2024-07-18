function initAddressSearch(zipSelector, searchButtonSelector, prefectureSelector, citySelector, addressSelector) {
    $(searchButtonSelector).on('click', function() {
        var zip = $(zipSelector).val().replace(/-/g, '').replace(/[^0-9]/g, '');
        if (zip.length === 7) {
            $.ajax({
                url: "https://zipcloud.ibsnet.co.jp/api/search",
                dataType: "jsonp",
                data: { zipcode: zip },
                success: function(data) {
                    if (data.results) {
                        $(prefectureSelector).val(data.results[0].address1);
                        $(citySelector).val(data.results[0].address2);
                        $(addressSelector).val(data.results[0].address3);
                    } else {
                        clearAddressFields();
                        alert('該当する住所情報が見つかりませんでした。');
                    }
                },
                error: function() {
                    clearAddressFields();
                    alert('住所情報の取得に失敗しました。');
                }
            });
        } else {
            clearAddressFields();
            alert('正しい郵便番号を入力してください。');
        }
    });

    function clearAddressFields() {
        $(prefectureSelector).val('');
        $(citySelector).val('');
        $(addressSelector).val('');
    }
}