<template>
  <div class="section section-tabs">
    <div class="container">
      <div class="title">
        <h4>Nos services</h4>
      </div>
      <div class="row">
        <div class="col-md-10 ml-auto col-xl-10 mr-auto">
          <p class="category"></p>
          <!-- Nav tabs -->
          <card>
            <tabs
              slot="raw-content"
              tab-content-classes="tab-content-padding text-center"
            >
              <tab-pane>
                <template slot="label">
                  <i class="now-ui-icons objects_umbrella-13"></i> Soins
                </template>
                <p>
                  I think that’s a responsibility that I have, to push
                  possibilities, to show people, this is the level that things
                  could be at. So when you get something that has the name Kanye
                  West on it, it’s supposed to be pushing the furthest
                  possibilities. I will be the leader of a company that ends up
                  being worth billions of dollars, because I got the answers. I
                  understand culture. I am the nucleus.
                </p>
              </tab-pane>
              <tab-pane>
                <template slot="label">
                  <i class="now-ui-icons shopping_cart-simple"></i> Esthetique
                </template>
                <p>
                  I will be the leader of a company that ends up being worth
                  billions of dollars, because I got the answers. I understand
                  culture. I am the nucleus. I think that’s a responsibility
                  that I have, to push possibilities, to show people, this is
                  the level that things could be at. I think that’s a
                  responsibility that I have, to push possibilities, to show
                  people, this is the level that things could be at.
                </p>
              </tab-pane>
              <tab-pane>
                <template slot="label">
                  <i class="now-ui-icons shopping_shop"></i> Orthodontie
                </template>
                <p>
                  I think that’s a responsibility that I have, to push
                  possibilities, to show people, this is the level that things
                  could be at. So when you get something that has the name Kanye
                  West on it, it’s supposed to be pushing the furthest
                  possibilities. I will be the leader of a company that ends up
                  being worth billions of dollars, because I got the answers. I
                  understand culture. I am the nucleus.
                </p>
              </tab-pane>
              <tab-pane>
                <template slot="label">
                  <i class="now-ui-icons ui-2_settings-90"></i> Prothèses dentaires
                </template>
                <p>
                  "I will be the leader of a company that ends up being worth
                  billions of dollars, because I got the answers. I understand
                  culture. I am the nucleus. I think that’s a responsibility
                  that I have, to push possibilities, to show people, this is
                  the level that things could be at."
                </p>
              </tab-pane>
            </tabs>
          </card>
        </div>

      </div>
    </div>
  </div>
</template>
<script>
import { Card, Tabs, TabPane } from '@/components';

export default {
  components: {
    Card,
    Tabs,
    TabPane
  }
};
</script>
<style  lang="scss">
.tab-content.tab-content-padding {
  padding: 20px;
}
.section-tabs{
  text-align: center;
}
.nav{
  justify-content:space-around;
}
</style>
