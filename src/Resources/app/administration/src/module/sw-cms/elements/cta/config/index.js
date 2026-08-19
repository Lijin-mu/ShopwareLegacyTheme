import template from "./sw-cms-el-config-cta.html.twig";
import "./sw-cms-el-config-cta.scss";

const { Component, Mixin } = Shopware;

Component.register("sw-cms-el-config-cta", {
  template,

  inject: ["repositoryFactory"],

  mixins: [Mixin.getByName("cms-element")],

  data() {
    return {
      mediaModalIsOpenDesktop: false,
      mediaModalIsOpenTablet: false,
      mediaModalIsOpenMobile: false,
    };
  },

  computed: {
    mediaRepository() {
      return this.repositoryFactory.create("media");
    },

    uploadTagDesktop() { return `cms-cta-desktop-${this.element.id}`; },
    uploadTagTablet() { return `cms-cta-tablet-${this.element.id}`; },
    uploadTagMobile() { return `cms-cta-mobile-${this.element.id}`; },

    previewSourceDesktop() { return this.element.data?.mediaDesktop || this.element.config.mediaDesktop.value; },
    previewSourceTablet() { return this.element.data?.mediaTablet || this.element.config.mediaTablet.value; },
    previewSourceMobile() { return this.element.data?.mediaMobile || this.element.config.mediaMobile.value; },

    contentUpdate: {
      get() { return this.element.config.content.value; },
      set(value) { this.element.config.content.value = value; },
    },
  },

  created() {
    this.createdComponent();
  },

  methods: {
    createdComponent() {
      this.initElementConfig("cta");
    },

    onElementUpdate(value) {
      this.element.config.content.value = value;
      this.$emit("element-update", this.element);
    },

    async onImageUploadDesktop({ targetId }) {
      const media = await this.mediaRepository.get(targetId);
      this.element.config.mediaDesktop.value = media.id;
      this.updateElementData('mediaDesktop', media);
      this.$emit("element-update", this.element);
    },
    onImageRemoveDesktop() {
      this.element.config.mediaDesktop.value = null;
      this.updateElementData('mediaDesktop', null);
      this.$emit("element-update", this.element);
    },
    onSelectionChangesDesktop(mediaEntity) {
      this.onImageUploadDesktop({ targetId: mediaEntity[0].id });
    },
    onOpenMediaModalDesktop() { this.mediaModalIsOpenDesktop = true; },
    onCloseModalDesktop() { this.mediaModalIsOpenDesktop = false; },

    async onImageUploadTablet({ targetId }) {
      const media = await this.mediaRepository.get(targetId);
      this.element.config.mediaTablet.value = media.id;
      this.updateElementData('mediaTablet', media);
      this.$emit("element-update", this.element);
    },
    onImageRemoveTablet() {
      this.element.config.mediaTablet.value = null;
      this.updateElementData('mediaTablet', null);
      this.$emit("element-update", this.element);
    },
    onSelectionChangesTablet(mediaEntity) {
      this.onImageUploadTablet({ targetId: mediaEntity[0].id });
    },
    onOpenMediaModalTablet() { this.mediaModalIsOpenTablet = true; },
    onCloseModalTablet() { this.mediaModalIsOpenTablet = false; },

    async onImageUploadMobile({ targetId }) {
      const media = await this.mediaRepository.get(targetId);
      this.element.config.mediaMobile.value = media.id;
      this.updateElementData('mediaMobile', media);
      this.$emit("element-update", this.element);
    },
    onImageRemoveMobile() {
      this.element.config.mediaMobile.value = null;
      this.updateElementData('mediaMobile', null);
      this.$emit("element-update", this.element);
    },
    onSelectionChangesMobile(mediaEntity) {
      this.onImageUploadMobile({ targetId: mediaEntity[0].id });
    },
    onOpenMediaModalMobile() { this.mediaModalIsOpenMobile = true; },
    onCloseModalMobile() { this.mediaModalIsOpenMobile = false; },

    updateElementData(key, media) {
      if (!this.element.data) {
        this.element.data = {};
      }
      this.element.data[key] = media;
    }
  },
});