const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const overlay = document.getElementById('overlay');
    
    // Alternar menÃº
    function toggleMenu() {
      sidebar.classList.toggle('closed');
      content.classList.toggle('centered');
      
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('open');
        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
      }
    }
    
    menuToggle.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
    
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
      } else if (window.innerWidth <= 768) {
        sidebar.classList.add('closed');
        content.classList.add('centered');
      }
    });

    if (window.innerWidth <= 768) {
      sidebar.classList.add('closed');
      content.classList.add('centered');
    }

    // AnimaciÃ³n de dashboards
    function animateValue(id, start, end, duration) {
      const obj = document.getElementById(id);
      let startTime = null;

      function step(timestamp) {
        if (!startTime) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        obj.textContent = (id === "promedio") 
          ? (start + (end - start) * progress).toFixed(1) 
          : Math.floor(start + (end - start) * progress);
        if (progress < 1) {
          requestAnimationFrame(step);
        }
      }
      requestAnimationFrame(step);
    }

    // CrÃ©ditos y promedio inventados
    window.onload = function() {
      animateValue("creditos", 0, 220, 2000);
      animateValue("promedio", 0, 9.3, 2000);
    };
    function animateCircle(id, textId, target, isDecimal=false) {
      const circle = document.getElementById(id);
      const number = document.getElementById(textId);
      const radius = circle.r.baseVal.value;
      const circumference = 2 * Math.PI * radius;

      circle.style.strokeDasharray = circumference;
      circle.style.strokeDashoffset = circumference;

      let progress = 0;
      let step = 0;

      const interval = setInterval(() => {
        if (progress >= target) {
          clearInterval(interval);
        } else {
          progress += 1;
          step = (progress / 100) * circumference;
          circle.style.strokeDashoffset = circumference - step;
          number.textContent = isDecimal ? (progress/10).toFixed(1) : progress;
        }
      }, 20);
    }

    // CrÃ©ditos: 220 â†’ lo convertimos a % (ejemplo: 220 de 300 = 73%)
    animateCircle("progressCreditos", "creditos", 73);

    // Promedio: 9.3 â†’ lo convertimos a % (93%)
    animateCircle("progressPromedio", "promedio", 93, true);
