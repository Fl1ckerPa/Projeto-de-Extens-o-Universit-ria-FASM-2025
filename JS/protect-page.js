/**
 * Script para proteger páginas restritas
 * Deve ser incluído em todas as páginas que requerem autenticação
 */

document.addEventListener('DOMContentLoaded', async () => {
    // Aguardar auth.js ser carregado
    if (!window.authManager) {
        console.error('auth.js não foi carregado. Certifique-se de incluir auth.js antes de protect-page.js');
        return;
    }
    
    // Verificar autenticação e proteger página
    const isAuthenticated = await authManager.protectPage(true);
    
    if (!isAuthenticated) {
        // Redirecionamento será feito pelo protectPage
        return;
    }
    
    // Se autenticado, atualizar navegação
    await authManager.updateNavigation();
});

