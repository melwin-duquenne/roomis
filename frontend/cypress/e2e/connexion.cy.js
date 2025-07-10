describe('Authentification: Inscription et Connexion', () => {
  const testEmail = `user${Date.now()}@test.com`;
  const testPassword = 'Test1234!';
  const testPseudo = `TestUser${Date.now()}`;

  beforeEach(() => {
    cy.visit('http://localhost:5173/');
  });

  it('ouvre le modal d’authentification', () => {
    cy.contains('Connexion').click(); // Bouton du header
    cy.get('.loginapp').should('exist');
  });

  it('remplit le formulaire d’inscription et s’inscrit', () => {
    cy.contains('Connexion').click();
    cy.contains('Inscription').click();

    cy.get('[data-testid="register-pseudo"]').eq(0).type(testPseudo); // Pseudo
    cy.get('input[type="email"]').type(testEmail); // Email
    cy.get('input[type="password"]').eq(0).type(testPassword); // Password
    cy.get('input[type="password"]').eq(1).type(testPassword); // Confirm password

    cy.get('form').submit();

    cy.contains('Inscription réussie !', { timeout: 20000 }).should('exist');
  });

  it('se connecte and compte management', () => {
    cy.contains('Connexion').click();

    cy.get('input[type="email"]').type(testEmail);
    cy.get('input[type="password"]').type(testPassword);

    cy.get('form').submit();

    cy.contains('Connexion réussie !', { timeout: 20000 }).should('exist');

    // Vérifie que le cookie JWT est bien présent
    cy.getCookie('jwt').should('exist');

    cy.get('a[href="/profile"]',  { timeout: 10000 }).should('exist');
    // Clique sur le lien profil et vérifie l’URL
    cy.get('a[href="/profile"]').click();
    cy.url().should('include', '/profile');

    cy.get('[data-testid="update-prenom"]', { timeout: 10000 }).type('Jean');
    cy.get('[data-testid="update-Nom"]').type('Dupont');
    cy.get('[data-testid="update-pseudo"]').type('jdupont75');

    // Optionnel : changer le mot de passe
    cy.get('[data-testid="update-new-password"]').type('NewStrongPass1!');
    cy.get('[data-testid="update-confirm-password"]').type('NewStrongPass1!');

    // Envoie le formulaire
    cy.contains('Mettre à jour').click();

    cy.contains('Profil mis à jour avec succès !', { timeout: 10000 }).should('be.visible');

    });

  });
  it('profil existant', () => {

    
});
