<section class="agenda">
  <h1>Mon agenda</h1>

  <button id="btn-open-modal">Nouv. Event</button>

  <div id="modalCreateEvent" class="{{#modal_open}}open{{/modal_open}}">
    <form method="post" action="{{create_url}}">
      {{{csrf_input}}}

      <table>
        <tr>
          <th><h2>Créer un Event</h2></th>
          <th><button id="closeModal" type="button">X</button></th>
        </tr>
      </table>

      <table>
        <label>Intitulé</label>
        <input type="text" name="titre" placeholder="..." required>

        <label>Date</label>
        <input type="date" name="date" required>

        <label>Horaire</label>
        <input type="time" name="heure" required>

        <label>Lieu</label>
        <input type="text" name="lieu" placeholder="..." required>
      </table>

      <button type="submit">Créer l'évenement</button>
    </form>
  </div>

  <div class="navigation">
    <a href="{{prev_week_url}}">&lt; Semaine précédente</a>
    <span>Semaine du {{week_label}}</span>
    <a href="{{next_week_url}}">Semaine suivante &gt;</a>
  </div>

  <table class="agenda-grid">
    <thead>
      <tr>
        <th>Heure</th>
        {{#each days}}
          <th>{{name}}<br>({{date}})</th>
        {{/each}}
      </tr>
    </thead>
    <tbody>
      {{#each hours}}
        <tr>
          <td class="time-slot">{{display}}</td>
          {{#each days}}
            <td class="cell {{#has_events}}event-cell{{/has_events}}">
              {{#events}}
                <div class="event {{css_class}}"
                     style="height: {{height_px}}px; top: {{top_px}}px;">
                  <strong>{{title}}</strong><br>
                  ({{time}}, {{duration}}h)<br>
                  {{location}}

                  <div class="detail" hidden>
                    <h2>Détails de l'événement</h2>
                    <p>Intitulé : {{title}}</p>
                    <p>Date : {{date}}</p>
                    <p>Horaire : {{time}}</p>
                    <p>Lieu : {{location}}</p>
                    <button class="closeDetail" type="button">Fermer</button>
                  </div>
                </div>
              {{/events}}
            </td>
          {{/each}}
        </tr>
      {{/each}}
    </tbody>
  </table>
</section>
