<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="imagens/icone.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS da homepage -->
    <link rel="stylesheet" href="css/homepage.css">

    <!-- CSS do cookie -->
    <link rel="stylesheet" href="css/style_cookies.css">

    <!-- SCRPIT do cookie -->
    <link rel="stylesheet" href="js/script_cookies.js">

    <!-- CSS do carrosel dos professores -->
    <link rel="stylesheet" href=" {{ asset('css/swiper-bundle.min.css') }}">

    <!-- CSS do carrossel -->
    <link rel="stylesheet" href="{{ asset('css/slider.css') }}">

    <!-- CSS do FAQ -->
    <link rel="stylesheet" href="css/faq.css">
    <link rel="stylesheet" href="css/footer.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>

    <!-- Comparação de Preços-->
    <link rel="stylesheet" href="css/precos.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <title>Intelecto - Homepage</title>
    @if(auth()->user()->nivel === 'admin')
       @include('layout._cabecalho_admin')
    @elseif(auth()->user()->nivel === 'usuario')
      @include('layout._cabecalho')
    @elseif(auth()->user()->nivel === 'professor')
      @include('layout._cabecalho_professor')
    @endif

        <!-- ////////////////////////////////////////////     -->
        <main> <!-- conteudo principal da homepage -->
            <section class="container_main">
                <div class="main-text">
                    <img src="imagens/logo_intelecto.svg" alt="Logo Intelecto" id="imgLogo">
                    <h2>Homepage:</h2>
                    <p>Bem-vindo à nossa escola virtual, o seu destino para uma educação de excelência e personalizada! Na busca pelo sucesso acadêmico, oferecemos aulas particulares que se adaptam ao seu ritmo de aprendizado, bem como cursos preparatórios específicos para o CTI/ETEC, concursos e vestibulares. Nossa equipe de educadores altamente qualificados está comprometida em ajudá-lo a atingir seus objetivos educacionais. Aqui, acreditamos que cada aluno é único, e é por isso que personalizamos cada experiência de aprendizado para atender às suas necessidades individuais. Prepare-se para alcançar resultados brilhantes conosco.</p>
                </div>
                <div class="main-img">
                    <img src="imagens/imagem_homepage.png" alt="imagem_homepage">
                </div>
            </section>
        </main>

        <!-- //////////////FIM DO CONTEUDO PRINCIPAL DA HOMEPAGE/////////////////// -->
        
        <!-- //////////////COMEÇO DO COOKIES//////////////////////////////     -->

        <div class="wrapper_cookie">
          <header class="header_cookie">
            <i class="bx bx-cookie"></i>
            <h2>Termos e Cookies</h2>
          </header>

          <div class="data">
            <p>Este site utiliza cookies para ajudá-lo a ter uma experiência de navegação superior e mais relevante no site. <a href="{{ route('termos') }}"> Leia mais...</a></p>
          </div>

          <div class="buttons_cookie">
            <button class="button_cookie" id="acceptBtn">Ok</button>
          </div>
        </div>

        <!-- /////////////////////// fim do cookie //////////////////////// -->

        <div class="professores">
       
          <h1 class="titulo_homepage">Nossa Equipe:</h1>
          <p class="subtitulo_homepage">Nossa equipe de professores altamente qualificados oferece educação personalizada e atualizada, criando um ambiente de aprendizado motivador. Junte-se a nós para alcançar seus objetivos acadêmicos e profissionais com confiança e excelência.</p>
          <br><br>

          <div class="cardsProf">

          <div class="slide-container swiper">
            <div class="slide-content">
                <div class="card-wrapper swiper-wrapper">
                @foreach($rows as $row)
                    <div class="card swiper-slide">
                        <div class="image-content">
                            <span class="overlay"></span>

                            <div class="card-image">
                                <img src="{{ asset($row->imagem_professor) }}" alt="" class="card-img">
                            </div>
                        </div>

                        <div class="card-content">
                            <h2 class="name">{{$row->nome_professor}}</h2>
                            <p class="description">{{$row->descricao_professor}}</p>

                        </div>
                    </div>
                @endforeach
                </div>
                 
            </div>

            <div class="swiper-button-next swiper-navBtn"></div>
            <div class="swiper-button-prev swiper-navBtn"></div>

            </div>
            
          </div> <!-- Fim do container card dos professores -->
           
          <br><br>
        </div> <!--////////////////// Fim da sessão dos professores /////////////////// -->

        <!-- ///////////////////COMEÇO DA SECTION DE PERGUNTAS FREQUENTES///////////// -->

        <h1 class="titulofaq">Perguntas Frequentes:</h1>
        <p class="subtitulofaq">Encontre respostas para suas dúvidas mais comuns sobre nossos cursos preparatórios presenciais.</p>

        <div class="faq">
            <div class="accordion">
                <div class="image-box">
                  <img src="imagens/imagem_faq.png" alt="Accordion Image">
                </div>
                <div class="accordion-text">
                <ul class="faq-text">
                  <li>
                    <div class="question-arrow">
                      <span class="question">O acesso à plataforma é vitálico?</span>
                      <i class="bx bxs-chevron-down arrow"></i>
                    </div>
                    <p>Não, somente durante o período de pagamento do pacote.</p>
                    <span class="line"></span>
                  </li>
                  <li>
                    <div class="question-arrow">
                      <span class="question">De onde eu posso acessar a plataforma?</span>
                      <i class="bx bxs-chevron-down arrow"></i>
                    </div>
                    <p>De todos os dispositivos que contenham acesso à internet, porém, é recomendado o uso por meio de um computador, pois a tela é maior e facilita a visualização</p>
                    <span class="line"></span>
                  </li>
                  <li>
                    <div class="question-arrow">
                      <span class="question">Posso ter mais de uma conta por dispositivo?</span>
                      <i class="bx bxs-chevron-down arrow"></i>
                    </div>
                    <p>Sim, é possível ter mais de uma conta por dispositivo, basta fazer logout.</p>
                    <span class="line"></span>
                  </li>
                  <li>
                    <div class="question-arrow">
                      <span class="question">Como o pagamento pode ser realizado?</span>
                      <i class="bx bxs-chevron-down arrow"></i>
                    </div>
                    <p>Basta entrar em contato conosco para ter informações de preços e meios de pagamento.</p>
                    <span class="line"></span>
                  </li>
                </ul>
                </div>
              </div>
        </div>

        <!-- //////////////////FIM DA SECTION DE PERGUNTAS FREQUENTES/////////////////////////     -->

         <!-- ////////////////SECTION DA LOCALIZAÇÃO////////////////////     -->
        
        
        <h1 class="titulo_homepage_loc_escola">Chegue em nossa escola:</h1>
        <p class="subtitulo_homepage_loc_escola">Clique no mapa para obter informações sobre como chegar à nossa escola. Nossa unidade está localizada no centro da cidade, em proximidade à antiga estação ferroviária.</p>    
        <div class="localizacao" id="home">
                
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3690.657782447279!2d-49.08336622545902!3d-22.328778679666105!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94bf67acc762b0cb%3A0x40a9dadab71cb10f!2sIntelecto%20Bauru!5e0!3m2!1spt-BR!2sbr!4v1695593411541!5m2!1spt-BR!2sbr" class="framei"style="border:0;"></iframe>
                
        </div> 

          <!-- ////////////////////////////////FIM DA LOCALIZAÇÃO////////////     -->

          <!-- //////////////////////////SECTION DOS PLANOS DO INTELECTO////////////     -->
          <div class="planosInt">
            <h1 class="titulo_homepage_nossos_planos">Nossos Planos:</h1>
            <p class="subtitulo_homepage_nossos_planos">Seja bem-vindo à nossa escola, onde o sucesso é construído passo a passo! Descubra como nossos cursos preparatórios presenciais podem impulsionar o seu caminho para o êxito</p>
          </div>
         
         <article class="prices">
             <div class="wrapper">
                 <div class="table cti">
                  <div class="price-section">
                    <div class="price-area">
                      <div class="inner-area">
                        <span class="text">$</span>
                        <span class="price">75</span>
                      </div>
                    </div>
                  </div>
                  <div class="package-name"></div>
                  <ul class="features">
                    <li>
                      <span class="list-name">-Acesso às aulas</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-Acesso à plataforma</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-10 Correções de Redações mensais</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-1 Aula particular por mês</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                  </ul>
                </div>

                <div class="table militar">
                  <div class="price-section">
                    <div class="price-area">
                      <div class="inner-area">
                        <span class="text">$</span>
                        <span class="price">80</span>
                      </div>
                    </div>
                  </div>
                  <div class="package-name"></div>
                  <ul class="features">
                   <li>
                      <span class="list-name">-Acesso às aulas</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-Acesso à plataforma</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-25 Correções de Redações mensais</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-2 Aulas particulares por mês</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                  </ul>
                  {{-- <div class="btn"><button>Adquirir</button></div> --}}
                </div>

                <div class="table redacao">
                  <div class="price-section">
                    <div class="price-area">
                      <div class="inner-area">
                        <span class="text">$</span>
                        <span class="price">100</span>
                      </div>
                    </div>
                  </div>
                  <div class="package-name"></div>
                  <ul class="features">
                    <li>
                      <span class="list-name">-Acesso às aulas</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-Acesso à plataforma</span>
                      <span class="icon check"><i class="fas fa-check"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-Ilimitadas Correções de Redações</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                    <li>
                      <span class="list-name">-5 Aula particulares por mês</span>
                      <span class="icon cross"><i class="fas fa-times"></i></span>
                    </li>
                  </ul>
                  {{-- <div class="btn"><button>Adquirir</button></div> --}}
                </div>


              </div>
         </article>

         <!-- /////////////////FIM DA SECTION DE PREÇOS E PLANOS///////////////////////////     -->

         <!-- /////////////// SCRPIT PARA A JANELA DE COOKIES E DO FAQ /////////////// -->
         <script>
            const cookieBox = document.querySelector(".wrapper_cookie"),
            buttons = document.querySelectorAll(".button_cookie");

          const executeCodes = () => {
            if (document.cookie.includes("codinglab")) return;
            cookieBox.classList.add("show");

            buttons.forEach((button) => {
              button.addEventListener("click", () => {
                cookieBox.classList.remove("show");
                if (button.id == "acceptBtn") {
                  document.cookie = "cookieBy= codinglab; max-age=" + 60 * 60 * 24 * 30;
                }
              });
            });
          };
          window.addEventListener("load", executeCodes);
         </script>
         <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
         <script src="{{ asset('js/swiper-bundle.min.js') }}"></script>
        <script src="js/script.js"></script>
        <script>
          let li = document.querySelectorAll(".faq-text li");
          for (var i = 0; i < li.length; i++) {
            li[i].addEventListener("click", (e)=>{
              let clickedLi;
              if(e.target.classList.contains("question-arrow")){
                clickedLi = e.target.parentElement;
              }else{
                clickedLi = e.target.parentElement.parentElement;
              }
             clickedLi.classList.toggle("showAnswer");
            });
          }
        </script>
        <!-- //////////////////////<<<<<FIM DO SCRIPT>>>>>>////////////////////// -->


          <!-- ///////////////////////// AQUI É CHAMADO O RODAPÉ DO SITE COM BASE NO NÍVEL DE LOGIN //////////////////////// -->
    @if(auth()->user()->nivel === 'admin')
       @include('layout._rodape_admin')
    @elseif(auth()->user()->nivel === 'usuario')
      @include('layout._rodape')
    @elseif(auth()->user()->nivel === 'professor')
      @include('layout._rodape_professor')
    @endif
