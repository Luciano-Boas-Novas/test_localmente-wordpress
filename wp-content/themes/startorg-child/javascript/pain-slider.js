const painSlider = document.querySelector('.pain-slider')
const painList = document.querySelector('.pain-list')
const allScreen = document.querySelector('.row-list-slider')
if (painSlider && painList) {
  const firstPainList = painList.firstElementChild
  firstPainList.classList.add('pain-list-active')
  
  const sliderElements = painSlider.querySelector('.n2-ss-slider-4')
  const arrows = painSlider.querySelectorAll('.nextend-arrow')
  if (sliderElements && arrows.length > 0) {
    // Clique nas setas
    arrows.forEach((arrow) => {
      arrow.addEventListener('click', (e) => {

        // Desabilita seta, bullets e lista
        allScreen.classList.add('disabled-pointer')

        let targetElement;
        const activeCard = painSlider.querySelector('.n2-ss-slide-active')
        if (activeCard) {
          activeCard.classList.remove('pain-list-active')
          if (e.target.alt == 'next arrow') {
            if (activeCard.nextSibling.nodeName != 'DIV') {
              targetElement = sliderElements.children[1]
            } else {
              targetElement = activeCard.nextSibling
            }
          } else {
            if (activeCard.previousSibling.nodeName == 'svg') {
              targetElement = sliderElements.lastElementChild
            } else {
              targetElement = activeCard.previousSibling
            }
          }
        }

        // Remove o foco do item na lista
        const currentActive = painList.querySelector('.' + activeCard.attributes['data-title'].nodeValue)
        if (currentActive.classList.contains('pain-list-active')) {
          currentActive.classList.remove('pain-list-active')
        }

        const element = painList.querySelector('.' + targetElement.attributes['data-title'].nodeValue)
        setTimeout(() => {
          // Adiciona foco no item selecionado
          element.classList.add('pain-list-active')

          // Habilita seta, bullets e lista
          allScreen.classList.remove('disabled-pointer')
        }, '700')
      })
    })

    // Clique na lista
    painList.querySelectorAll('li').forEach((item) => {
      item.addEventListener('click', (e) => {
        const activeCard = painSlider.querySelector('.n2-ss-slide-active')
        const bulletItem = document.querySelector(`div[aria-label="${item.classList[0]}"]`)

        // Desabilita seta, bullets e lista
        allScreen.classList.add('disabled-pointer')

        // Retira foco do item da lista
        const currentActive = painList.querySelector('.' + activeCard.attributes['data-title'].nodeValue)
        if (currentActive.classList.contains('pain-list-active')) {
          currentActive.classList.remove('pain-list-active')
        }

        if (activeCard && bulletItem) {
          bulletItem.click()
          setTimeout(() => {
            // Adiciona foco no item selecionado e move o slider
            item.classList.add('pain-list-active')

            // Habilita seta, bullets e lista
            allScreen.classList.remove('disabled-pointer')
          }, '400')
        }
      })
    })
  }

  // Bullets de navegação
  function buildListener(elements) {
    const navBullets = elements
    if (navBullets) {
      navBullets.forEach((bullet) => {
        bullet.addEventListener('click', (e) => {
          // Remove o foco do item atual na lista
          const currentActive = painList.querySelector('.pain-list-active')

          // Desabilita seta, bullets e lista
          allScreen.classList.add('disabled-pointer')

          if (currentActive) {
            currentActive.classList.remove('pain-list-active')
          }

          const bulletSelected = painList.querySelector(`.${e.target.ariaLabel}`)
          if (bulletSelected) {
            setTimeout(() => {
              // Adiciona o foco na lista do bullet clicado
              bulletSelected.classList.add('pain-list-active')

              // Habilita seta, bullets e lista
              allScreen.classList.remove('disabled-pointer')
            }, '400')
          }
        })
      })
    }
  }

  function waitForElements(selector, timeout = 5000) {
    return new Promise((resolve, reject) => {
      const elements = document.querySelectorAll(selector)
      if (elements.length > 1) {
        return resolve(elements)
      }
  
      const observer = new MutationObserver(() => {
        const elements = document.querySelectorAll(selector)
        if (elements.length > 1) {
          observer.disconnect()
          resolve(elements)
        }
      })
  
      observer.observe(document.body, { childList: true, subtree: true })
  
      setTimeout(() => {
        observer.disconnect()
        reject(new Error(`Elemento ${selector} não encontrado dentro do tempo limite.`))
      }, timeout)
    })
  }
  
  // Uso da função
  waitForElements('.n2-bullet')
    .then(elements => {
      buildListener(elements)
    })
    .catch(error => {
      console.error(error)
    })
}
