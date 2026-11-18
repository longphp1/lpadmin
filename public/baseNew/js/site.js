// document.querySelector('#prev-btn').onclick = function() {
//   slideGoods(-1)
// }
// document.querySelector('#next-btn').onclick = function() {
//   slideGoods(1)
// }
// function slideGoods(step) {
//   const list = document.querySelector('#img-list')
//   const width = list.querySelector('img').width
//   if (step > 0) {
//     if (parseInt(list.style.left || 0) > width - list.getBoundingClientRect().width) {
//       list.style.left = (parseInt(list.style.left || 0) - width) + 'px'
//     } else {
//       list.style.left = '0'
//     }
//   } else {
//     if (parseInt(list.style.left || 0) < 0) {
//       list.style.left = (parseInt(list.style.left || 0) + width) + 'px'
//     } else {
//       list.style.left = width - list.getBoundingClientRect().width + 'px'
//     }
//   }
// }
document.querySelectorAll('#faq .list .item').forEach(item  => {
    item.addEventListener('click', () => {
        if (item.classList.contains('active')) {
            item.classList.remove('active')
        } else {
            item.classList.add('active')
        }
    })
})
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleBtn');
    const modalOverlay = document.getElementById('modalOverlay');
    const closeBtn = document.querySelector('.close-btn');

// 显示模态框
    toggleBtn.addEventListener('click', function() {
        modalOverlay.classList.add('active');
    });

// 关闭模态框
    closeBtn.addEventListener('click', function() {
        modalOverlay.classList.remove('active');
    });

// 点击 overlay 背景关闭模态框
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            modalOverlay.classList.remove('active');
        }
    });
});

window.onload = () => {
    initTestimonials()
    initFAQ()
    initFooter()
    //initBuy()
    initQualityCheck()
    initMoreCollapse()
    initDiscord()
}

function initTestimonials() {
    const testimonialsGrid = document.getElementById('testimonials-grid')
    if (!testimonialsGrid) return
    const arr = [
        {
            avatar: './images/avatar-1.png',
            name: 'Jerg',
            about: 'Blomi Discord Mod',
            text: 'Hands DOWN the most innovative way to find reps'
        },
        {
            avatar: './images/avatar-4.png',
            name: 'Spawn',
            about: 'Blomi Discord Mod',
            text: 'I\'ve probably saved hundreds of dollars throughout the years with Cedaz\'s finds. Gotta give him & nick credit for putting this together'
        },
        {
            avatar: './images/avatar-7.png',
            name: 'JSTN',
            about: 'Pro Monster Drinker',
            text: 'I don\'t buy reps, but cool site'
        },
        {
            avatar: './images/avatar-2.png',
            name: 'Waz',
            about: 'Stylist',
            text: 'Nick and Cedaz got the best finds fs'
        },
        {
            avatar: './images/avatar-5.png',
            name: 'Kyle',
            about: 'Pro Monster Drinker',
            text: 'This is the future for finding reps, I don\'t see spreadsheet lasting with stuff like this around'
        },
        {
            avatar: './images/avatar-8.png',
            name: 'Jake',
            about: 'Pro Monster Drinker',
            text: 'I\'ve probably saved hundreds of dollars throughout the years with Cedaz\'s finds. Gotta give him & nick credit for putting this together'
        },
        {
            avatar: './images/avatar-3.png',
            name: 'Kyle',
            about: 'Pro Monster Drinker',
            text: 'The easiest way to find reps 100%'
        },
        {
            avatar: './images/avatar-6.png',
            name: 'Jake',
            about: 'Pro Monster Drinker',
            text: 'Been buying reps for over 4 years, besides the reddit there\'s never been an easy way to find stuff. Glad this is around now.'
        },
        {
            avatar: './images/avatar-9.png',
            name: 'Jake',
            about: 'Pro Monster Drinker',
            text: 'Nick, Cedaz and Gilly are all time goats, gotta love what they put out'
        }
    ]
    const html = arr.map(item => {
        return `<div class="item">
      <div class="user flex">
        <img src="${item.avatar}" alt="" class="avatar">
        <div class="auto">
          <div class="name">${item.name}</div>
          <div class="about">${item.about}</div>
        </div>
      </div>
      <div class="text">${item.text}</div>
    </div>`
    }).join('')
    testimonialsGrid.innerHTML = html
}

function initFAQ() {
    const faqList = document.getElementById('faq-list')
    if (!faqList) return
    const arr = [
        {
            question: 'What is Kakobuysheet?',
            answer: 'Kakobuysheet is in a sense, a spreadsheet made easy.We combine the best element’s of spreadsheets and top of the line website UI to make your shopping experience very easy.'
        },
        {
            question: 'How to get more deals?',
            answer: 'Register new users and get a $140 coupon +10% logistics discount coupon. It is recommended to register a new user for each purchase.'
        },
        {
            question: 'Is it easy to use?',
            answer: 'Kakobuysheet allows for the easiest finding & buying experience for new and veteran replica buyers. With thousands of finds, you’ll never need to go anywhere else.'
        },
        {
            question: 'Can I use mobile?',
            answer: 'Yes! Of Course! Just make sure you make an account with your agent of choice so it opens correctly.'
        },
        {
            question: 'Can I buy reps without an agent?',
            answer: 'Buying without an agent is a common practice and can be done extremely easily. Most people use middlemen or forwarders to make singular purchases!'
        },
    ]
    const html = arr.map(item => {
        return `<div class="item flex">
      <img src="/img/plus.png" alt="">
      <div class="auto">
        <div class="question">${item.question}</div>
        <div class="answer">${item.answer}</div>
      </div>
    </div>`
    }).join('')
    faqList.innerHTML = html
    document.querySelectorAll('#faq-list .item').forEach(item  => {
        item.addEventListener('click', () => {
            if (item.classList.contains('active')) {
                item.classList.remove('active')
            } else {
                item.classList.add('active')
            }
        })
    })
}

function initFooter() {
    const footer = document.getElementById('footer')
    if (!footer) return
    const arr = [
        'Kakobuysheet.com does not sell any physical products and neither does Kakobuy.',
        'This is purely link\'s to Taobao and Weidian to better educate buyers so they do not get scammed. csfanssheet.com does not support and endorse the sale of counterfeit goods and hopes this information is only used to aid buyers to not get scammed.'
    ]
    arr.forEach(item => {
        const div = document.createElement('div')
        div.innerHTML = item
        footer.appendChild(div)
    })
}

// function initBuy() {
//   const buy = document.getElementsByClassName('buy')
//   if (buy && buy.length > 0) {
//     for (let i = 0; i < buy.length; i++) {
//       buy[i].innerHTML = 'View details and Buy on Kakobuy'
//     }
//   }
// }

function initQualityCheck() {
    const list = document.getElementById('quality-check-list')
    if (list) list.addEventListener('click', e => {
        if (e.target.tagName.toLowerCase() === 'img') {
            const mask = document.createElement('div')
            const titleText = document.createElement('div')
            titleText.classList.add('auto')
            titleText.innerText = 'Quality Check'
            const close = document.createElement('img')
            close.classList.add('close')
            close.src = '/img/close.png'
            close.addEventListener('click', () => mask.remove())
            const title = document.createElement('div')
            title.classList.add('title')
            title.appendChild(titleText)
            title.appendChild(close)

            const img = document.createElement('img')
            img.src = e.target.src
            img.style.display = 'block'

            const dialog = document.createElement('div')
            dialog.classList.add('dialog')
            dialog.appendChild(title)
            dialog.appendChild(img)

            mask.classList.add('mask')
            mask.addEventListener('click', () => mask.remove())
            mask.appendChild(dialog)
            document.body.appendChild(mask)
        }
    })
}

function initMoreCollapse() {
    const moreLink = document.getElementById('more-collapse')
    const tags = document.getElementsByClassName('more-tag')
    if (moreLink && tags && tags.length > 0) {
        moreLink.addEventListener('click', () => {
            for (let i = 0; i < tags.length; i++) {
                tags[i].classList.toggle('show')
            }
        })
    }
}

function initDiscord() {
    const dialog = document.getElementById('join-discord')
    const close = document.getElementById('discord-close')
    const btnList = document.getElementsByClassName('join-btn')
    if (dialog && close) {
        close.addEventListener('click', () => dialog.classList.remove('show'))
    }
    if (btnList) {
        for (let i = 0; i < btnList.length; i++) {
            btnList[i].addEventListener('click', () => dialog.classList.add('show'))
        }
    }
}

function copyText(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);

    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert(`${text} copied successfully`)
}


