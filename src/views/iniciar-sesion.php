<div style="max-width: 400px; margin: 50px auto; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px;">
   <h1>Iniciar Sesión</h1>
   
   <form method="POST" action="/trayectoria_cucea/login">
      <div style="margin-bottom: 15px;">
         <label for="email">Email</label>
         <input type="email" id="email" name="email" class="input" required>
      </div>
      
      <div style="margin-bottom: 15px;">
         <label for="password">Contraseña</label>
         <input type="password" id="password" name="password" class="input" required>
      </div>
      
      <button type="submit" class="button is-primary">Entrar</button>
   </form>
   
   <p style="margin-top: 20px;">¿No tienes cuenta? <a href="/trayectoria_cucea/register">Regístrate</a></p>
</div>
