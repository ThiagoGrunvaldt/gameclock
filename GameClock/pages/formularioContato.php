<?php
require_once '../resources/header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Fale Conosco</h1>
    
    <div class="card p-4">
        <form id="contatoForm" method="POST" action="/GameClock/resources/emailContato.php">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
                <div class="invalid-feedback">Por favor, insira seu nome.</div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Por favor, insira um e-mail válido.</div>
            </div>
            <div class="mb-3">
                <label for="mensagem" class="form-label">Mensagem</label>
                <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required></textarea>
                <div class="invalid-feedback">Por favor, insira sua mensagem.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('contatoForm').addEventListener('submit', function(event) {
        const form = event.target;
        const nome = form.nome.value.trim();
        const email = form.email.value.trim();
        const mensagem = form.mensagem.value.trim();

        let isValid = true;

        if (nome === "") {
            form.nome.classList.add('is-invalid');
            isValid = false;
        } else {
            form.nome.classList.remove('is-invalid');
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            form.email.classList.add('is-invalid');
            isValid = false;
        } else {
            form.email.classList.remove('is-invalid');
        }

        if (mensagem === "") {
            form.mensagem.classList.add('is-invalid');
            isValid = false;
        } else {
            form.mensagem.classList.remove('is-invalid');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
</script>

<?php
require_once '../resources/footer.php'; 
?>