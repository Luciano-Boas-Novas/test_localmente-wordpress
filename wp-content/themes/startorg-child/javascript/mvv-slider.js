// Script apenas para a página Quem somos
if (window.location.href.includes('/quem-somos/')) {
  // DESKTOP
  const sliderNameClass = '.mvv-slider'
  const listNameClass = '.mvv-list'
  const listClassActive = 'mvv-list-active'
  
  const slider = document.querySelector(sliderNameClass)
  const list = document.querySelector(listNameClass)
  
  // Promise para desabilitar a seta previous ao iniciar com o card Missão
  let activeCardPromise
  let previousPromise
  function waitForElements(targetSlider, selector, timeout = 5000) {
    return new Promise((resolve, reject) => {
      const element = targetSlider.querySelector(selector)
      if (element) {
        return resolve(element)
      }
      const observer = new MutationObserver(() => {
        const element = targetSlider.querySelector(selector)
        if (element) {
          observer.disconnect()
          resolve(element)
        }
      })
      observer.observe(document.body, { childList: true, subtree: true })
      setTimeout(() => {
        observer.disconnect()
        reject(new Error(`Elemento ${selector} do slider ${targetSlider.className} não foi encontrado dentro do tempo limite.`))
      }, timeout)
    })
  }
  waitForElements(slider, '.n2-ss-slide-active')
  .then(active => {
    activeCardPromise = active
    waitForElements(slider, '.nextend-arrow-previous')
    .then(button => {
      previousPromise = button
      if (activeCardPromise.attributes['data-title'].nodeValue == 'mission') {
        previousPromise.classList.add('display-none-important')
      }
    })
    .catch(error => {
      console.error(error)
    })
  })
  .catch(error => {
    console.error(error)
  })
  // Fim da Promise
  
  // Função para habilitar seta de acordo com o card
  function showArrows(targetSlider, arrows) {
    // Esse setTimeout serve para esperar o slider trocar o card ativo (.n2-ss-slide-active)
    setTimeout(() => {
      const targetCard = targetSlider.querySelector('.n2-ss-slide-active')
      // Desabilita as setas de acordo com item selecionado
      if (targetCard.attributes['data-title'].nodeValue == 'mission') {
        // Aparece só seta direita
        arrows[0].classList.add('display-none-important')
        arrows[1].classList.remove('display-none-important')
  
      } else if (targetCard.attributes['data-title'].nodeValue == 'vision') {
        // Aparace ambas as setas
        arrows[0].classList.remove('display-none-important')
        arrows[1].classList.remove('display-none-important')
  
      } else if (targetCard.attributes['data-title'].nodeValue == 'values') {
        // Aparece só seta esquerda
        arrows[0].classList.remove('display-none-important')
        arrows[1].classList.add('display-none-important')
      }
    }, 500);
  }
  // Fim função
  
  if (slider && list) {
    const firstlist = list.firstElementChild
    firstlist.classList.add(listClassActive)
    
    const sliderElements = slider.querySelector('.n2-ss-slider-4')
    const arrows = slider.querySelectorAll('.nextend-arrow')
    if (sliderElements && arrows.length > 0) {
      // Clique na seta
      arrows.forEach((arrow) => {
        arrow.addEventListener('click', (e) => {
  
          // Desabilita setas
          arrows[0].classList.add('disabled')
          arrows[1].classList.add('disabled')
  
          let targetElement;
          const activeCard = slider.querySelector('.n2-ss-slide-active')
  
          if (activeCard) {
            activeCard.classList.remove(listClassActive)
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
  
          // Retira foco do item da lista
          const currentActive = list.querySelector('.' + activeCard.attributes['data-title'].nodeValue)
          if (currentActive.classList.contains(listClassActive)) {
            currentActive.classList.remove(listClassActive)
          }
  
          const element = list.querySelector('.' + targetElement.attributes['data-title'].nodeValue)
          setTimeout(() => {
            // Adiciona foco no item selecionado
            element.classList.add(listClassActive)
    
            // Habilita setas
            arrows[0].classList.remove('disabled')
            arrows[1].classList.remove('disabled')
          }, '700')
  
          showArrows(slider, arrows)
        })
      })
  
      // Clique na lista
      const mission = document.querySelector('.mission')
      const vision = document.querySelector('.vision')
      const values = document.querySelector('.values')
      if (mission && vision && values) {
        const mvvList = [mission, vision, values]
        mvvList.forEach((item) => {
          item.addEventListener('click', (e) => {
            const activeCard = slider.querySelector('.n2-ss-slide-active')
            const bulletItem = document.querySelector(`div[aria-label="${item.classList[0]}"]`)
  
            // Desabilita setas
            arrows[0].classList.add('disabled')
            arrows[1].classList.add('disabled')
  
            // Retira foco do item da lista
            const currentActive = list.querySelector('.' + activeCard.attributes['data-title'].nodeValue)
            if (currentActive.classList.contains(listClassActive)) {
              currentActive.classList.remove(listClassActive)
            }
  
            // Adiciona foco no item selecionado e move o slider
            if (activeCard && bulletItem) {
              bulletItem.click()
              setTimeout(() => {
                item.classList.add(listClassActive)
  
                // Habilita setas
                arrows[0].classList.remove('disabled')
                arrows[1].classList.remove('disabled')
              }, '400')
            }
  
            showArrows(slider, arrows)
          })
        })
      }
    }
  }
  // DESKTOP
  
  
  // MOBILE
  const sliderNameClassM = '.mvv-slider-mobile'
  const listClassActiveM = 'mvv-list-active'
  
  const sliderM = document.querySelector(sliderNameClassM)
  
  // Promise para desabilitar a seta previous ao iniciar com o card Missão
  // let activeCardPromiseM
  // let previousPromiseM
  // waitForElements(sliderM, '.n2-ss-slide-active')
  // .then(activeM => {
  //   activeCardPromiseM = activeM
  //   waitForElements(sliderM, '.nextend-arrow-previous')
  //   .then(buttonM => {
  //     previousPromiseM = buttonM
  //     if (activeCardPromiseM.attributes['data-title'].nodeValue == 'mission') {
  //       previousPromiseM.classList.add('display-none-important')
  //     }
  //   })
  //   .catch(error => {
  //     console.error(error)
  //   })
  // })
  // .catch(error => {
  //   console.error(error)
  // })
  // Fim da Promise
  
  // Desabilitando forçado, pois a Promise está demorando para carregar
  let previousPromiseM2 = sliderM.querySelector('.nextend-arrow-previous')
  previousPromiseM2.classList.add('display-none-important')
  
  if (sliderM) {
    const sliderElementsM = sliderM.querySelector('.n2-ss-slider-4')
    const arrowsM = sliderM.querySelectorAll('.nextend-arrow')
    if (sliderElementsM && arrowsM.length > 0) {
      // Clique na seta
      arrowsM.forEach((arrow) => {
        arrow.addEventListener('click', (e) => {
  
          // Desabilita setas
          arrowsM[0].classList.add('disabled')
          arrowsM[1].classList.add('disabled')
  
          let targetElementM;
          const activeCardM = sliderM.querySelector('.n2-ss-slide-active')
  
          if (activeCardM) {
            activeCardM.classList.remove(listClassActiveM)
            if (e.target.alt == 'next arrow') {
              if (activeCardM.nextSibling.nodeName != 'DIV') {
                targetElementM = sliderElementsM.children[1]
              } else {
                targetElementM = activeCardM.nextSibling
              }
            } else {
              if (activeCardM.previousSibling.nodeName == 'svg') {
                targetElementM = sliderElementsM.lastElementChild
              } else {
                targetElementM = activeCardM.previousSibling
              }
            }
          }
  
          setTimeout(() => {
            // Habilita setas
            arrowsM[0].classList.remove('disabled')
            arrowsM[1].classList.remove('disabled')
          }, '700')
  
          showArrows(sliderM, arrowsM)
        })
      })
    }
  }
}
