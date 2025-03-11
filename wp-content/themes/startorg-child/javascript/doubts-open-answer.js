document.addEventListener("DOMContentLoaded", () => {
  // Seleciona todos os botões de toggle e todas as respostas
  const toggleButtons = document.querySelectorAll(".doubt-cards-question");

  toggleButtons.forEach((toggleButton) => {
    const answer = toggleButton.closest('.doubt-cards').querySelector(".doubt-cards-answer");
    const arrowImage = toggleButton.querySelector("img");

    // Adiciona o evento de clique em cada botão
    toggleButton.addEventListener("click", () => {
      // Alterna a classe de visibilidade da resposta
      answer.classList.toggle("active");

      // Alterna a rotação da seta
      if (answer.classList.contains("active")) {
        arrowImage.style.transform = "rotate(90deg)";
      } else {
        arrowImage.style.transform = "rotate(0deg)";
      }
    });
  });
});
