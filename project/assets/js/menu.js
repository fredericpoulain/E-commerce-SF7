const allMenuCat = document.querySelectorAll('.menuCat');
const allUlMenuCat = document.querySelectorAll('.ulMenuCat');

allMenuCat.forEach(function (cat) {
    cat.addEventListener('click', (e) => {
        const nextElement = cat.nextElementSibling;
            if (nextElement.classList.contains('dropdown')){
                nextElement.classList.remove('dropdown')
            }else{
                allUlMenuCat.forEach(function (ul){
                    ul.classList.remove('dropdown')
                })
                nextElement.classList.add('dropdown')
            }



    })
});


