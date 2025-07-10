describe('template spec', () => {
  beforeEach(() => {
    cy.visit('http://localhost:5173/')
  })
  it('should display logo, search input and login button', () => {
    // Vérifie le logo (avec alt ou classe)
    cy.get('header img[alt="Logo"]').should('exist');

    // Vérifie le champ de recherche
    cy.get('header input[placeholder="Rechercher une room..."]').should('exist');

    // Vérifie le bouton Connexion (s'affiche si pas connecté)
    cy.get('header button').contains('Connexion').should('exist');
  });
   it('affiche les cartes des jeux', () => {
    // Vérifie que les jeux sont affichés
    cy.get('.game-card') // <-- Ajoute une classe `game-card` sur ton composant GameCard
      .should('have.length.at.least', 1);

    // Vérifie qu’un titre de jeu est affiché (ex : Morpion)
    cy.contains('Morpion').should('exist');
    cy.contains('Echec').should('exist');
  });
})