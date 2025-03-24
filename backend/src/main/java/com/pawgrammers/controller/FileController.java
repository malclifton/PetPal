package com.pawgrammers.controller;

import com.pawgrammers.service.S3Service;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

@Controller
public class FileController {

    private final S3Service s3Service;

    public FileController(S3Service s3Service) {
        this.s3Service = s3Service;
    }

    @GetMapping("/")
    public String index() {
        return "upload";
    }

    @PostMapping("/upload")
    public String handleFileUpload(@RequestParam("file") MultipartFile file, Model model) {
        try {
            String url = s3Service.uploadFile(file);
            model.addAttribute("url", url);
        } catch (Exception e) {
            model.addAttribute("error", "Upload failed: " + e.getMessage());
        }
        return "upload";
    }
}
